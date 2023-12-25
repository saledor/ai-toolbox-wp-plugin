#!/bin/bash

PLUGIN_DIR="./ai-toolbox"
BUNDLE_DIR="./bundle_plugin"
ZIP_FILE="ai-toolbox-$(date +%Y%m%d).zip"

# Create bundle directory if it doesn't exist
mkdir -p "$BUNDLE_DIR"

# Navigate to the plugin directory's parent
# cd "$(dirname "$PLUGIN_DIR")"

# Bundle the plugin directory into a zip file inside the bundle directory
zip -r "$BUNDLE_DIR/$ZIP_FILE" "$(basename "$PLUGIN_DIR")"

echo "Plugin bundled into zip file: $BUNDLE_DIR/$ZIP_FILE"
