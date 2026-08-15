#!/usr/bin/env bash
set -e

PLUGIN_SLUG="tasas-bcv-automaticas"
BUILD_DIR="build_staging"
OUTPUT_DIR="dist"
ZIP_FILE="${OUTPUT_DIR}/${PLUGIN_SLUG}.zip"

echo "=== Building release package for ${PLUGIN_SLUG} ==="

# 1. Lint PHP
echo "--> Checking PHP syntax..."
php -l tasas-bcv-automaticas.php

# 2. Compile i18n MO files
echo "--> Compiling language files..."
rm -f languages/*.mo
msgfmt -c -o languages/${PLUGIN_SLUG}-es_ES.mo languages/${PLUGIN_SLUG}-es_ES.po
msgfmt -c -o languages/${PLUGIN_SLUG}-en_US.mo languages/${PLUGIN_SLUG}-en_US.po


# 3. Prepare staging and output directories
rm -rf "${BUILD_DIR}" "${OUTPUT_DIR}"
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}" "${OUTPUT_DIR}"

# 4. Copy required production files
echo "--> Copying files to build folder..."
cp tasas-bcv-automaticas.php "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp readme.txt "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp README.md "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp LICENSE "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r languages "${BUILD_DIR}/${PLUGIN_SLUG}/"

# 5. Create zip file
echo "--> Packaging zip..."
ABS_ZIP="$(pwd)/${ZIP_FILE}"
(cd "${BUILD_DIR}" && zip -r "${ABS_ZIP}" "${PLUGIN_SLUG}")


# Clean temporary build staging
rm -rf "${BUILD_DIR}"

echo "=== Release build completed successfully! ==="
echo "Package saved to: ${ZIP_FILE}"
