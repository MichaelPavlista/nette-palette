<?php declare(strict_types=1);

/**
 * This file is part of the Nette Palette (https://github.com/MichaelPavlista/nette-palette)
 * Copyright (c) 2016 Michael Pavlistal (https://www.pavlista.cz)
 */

namespace NettePalette\Latte\Node;

use Generator;
use Latte\CompileException;
use Latte\Compiler\Nodes\Php\ArgumentNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

/**
 * Tag n:palette-src.
 */
final class PaletteSrcNode extends StatementNode
{
    /** @var ArgumentNode cesta k obrázku */
    public ArgumentNode $imagePath;

    /** @var ArgumentNode Palette query */
    public ArgumentNode $imageQuery;


    /**
     * Načtení parametrů a obsahu tagu.
     * @throws CompileException
     */
    public static function create(Tag $tag): self
    {
        $tag->expectArguments();
        $node = $tag->node = new self();

        // Tag je povolen pouze na HTML tagu img nebo source.
        if (
            !$tag->htmlElement?->name
            || !in_array(needle: mb_strtolower($tag->htmlElement->name), haystack: ['img', 'source']))
        {
            throw new CompileException(message: 'Tag n:palette-src is allowed only on html tag <img> or <source>.');
        }

        // Načtení argumentů.
        $args = $tag->parser->parseArguments()->toArguments();

        if (count($args) !== 2)
        {
            throw new CompileException(message: 'Tag n:palette-src requires exactly two arguments.');
        }

        $node->imagePath = $args[0];
        $node->imageQuery = $args[1];

        return $node;
    }


    /**
     * Vygenerování PHP kódu tagu.
     */
    public function print(PrintContext $context): string
    {
        return $context->format(
            <<<'PHP'
            echo ' src="', $this->global->palette->getUrl(%node, %node), '"';
            PHP,
            $this->imagePath,
            $this->imageQuery,
        );
    }


    /**
     * Vrací iterátor nad zanořenými částmi tagu.
     */
    public function &getIterator(): Generator
    {
        yield $this->imagePath;
        yield $this->imageQuery;
    }
}
