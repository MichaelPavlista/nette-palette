<?php declare(strict_types=1);

namespace NettePalette\Latte;

use Latte\CompileException;
use Latte\Compiler;
use Latte\Macro;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use Nette\Utils\Validators;

/**
 * LatteMacroSet
 * @package NettePalette\Latte
 * @url https://usefulangle.com/post/114/webp-image-in-html-with-fallback
 */
final class LatteMacroSet extends MacroSet
{
    /** @var bool je aktuálně vykreslován tag picture přes makro? */
    private $isInPicture = false;

    /** @var bool byl definován zdrojový obrázek picture setu? */
    private $isPictureSrcSet = false;


    /**
     * Provede instalaci palette maker do Latte compileru.
     * @param Compiler $compiler
     * @return void
     */
    public static function install(Compiler $compiler): void
    {
        $me = new LatteMacroSet($compiler);

        // Přidání makra pro vygenerování picture setu s WebP verzí obrázku.
        $me->addMacro('webp', [$me, 'macroWebpOpen'], [$me, 'macroWebpClose']);

        // Přidání makra pro definici zdrojového obrázku picture setu (tento obrázek se používá jako fallback pro staré prohlížeče).
        $me->addMacro('picture-src', null, null, [$me, 'macroPictureSrc']);
    }


    /**
     * Provedení akcí při začátku vykreslování picture setu.
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     */
    public function macroWebpOpen(MacroNode $node, PhpWriter $writer): string
    {
        $this->isInPicture = true;
        $this->isPictureSrcSet = false;

        return '';
    }


    /**
     * Provedení akcí při dokončení vykreslování picture setu.
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    public function macroWebpClose(MacroNode $node, PhpWriter $writer): string
    {
        $this->isInPicture = false;

        if (!$this->isPictureSrcSet)
        {
            throw new CompileException('Missing picture-src inside macro webp in ' . $node->getNotation());
        }

        //// Vygenerování source setů přes palette.
        $node->innerContent .= $writer->write(
            '<?php echo NettePalette\Latte\LatteHelpers::generatePictureSrcSetHtml($this->global->palette, $__palettePictureUrl); unset($__palettePictureUrl); ?>'
        );

        return '';
    }


    /**
     * Definice zdrojového obrázku pro picture set, tento obrázek se použije jako fallback.
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    public function macroPictureSrc(MacroNode $node, PhpWriter $writer): string
    {
        // Makro `picture-src` je možné používat pouze uvnitř makra `webp`.
        if (!$this->isInPicture)
        {
            throw new CompileException('Macro picture-src is only available inside macro webp in ' . $node->getNotation());
        }

        // Je možné definovat pouze jeden zdrojový obrázek pro picture set.
        if ($this->isPictureSrcSet)
        {
            throw new CompileException('Multiple picture-src in webp macro is forbidden in ' . $node->getNotation());
        }

        // Obrázek setu byl definován.
        $this->isPictureSrcSet = true;

        // Vygenerujeme PHP/HTML kód img tagu s fallbackem picture setu.
        return
            ' ?> src="<?php ' .
            $writer->write('$__palettePictureUrl=$this->global->palette->getPictureUrl(%node.args); echo $__palettePictureUrl->getPictureUrl(); ') .
            '?>"<?php ';
    }
}