### Prepare demo app for development.

# Script directory.
SCRIPT_DIR=$(dirname "$(realpath -s "$0")")

# Install nette-palette dependencies.
( cd "$SCRIPT_DIR/../../" && composer clear-cache && composer update --prefer-source -o )

# Install demo apps dependencies (${1//.}).
function installNetteDemo
{
  cd "$SCRIPT_DIR/../www/nette$1" && \
  composer update && \
  rm -rf "$SCRIPT_DIR/../www/nette$1/vendor/pavlista/nette-palette" "$SCRIPT_DIR/../www/nette$1/vendor/pavlista/palette" "$SCRIPT_DIR/../temp/$1" && \
  composer dump -o
}

installNetteDemo "2.5"
installNetteDemo "3.0"
