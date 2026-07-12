# TTE Blocks WordPress Plugin

**Version:** 1.1.0  
**Developer:** Systemik Solutions (https://systemiksolutions.com/)  
**License:** GPLv2

## Overview

This plugin provides custom blocks for the TTE site. It creates a custom block category "TTE Blocks", and provides the following block types in the WordPress block editor.

## Block Types

- **Omeka Term List:** This is a dynamic block which generates vocabulary terms from Omeka into a collection of colored tiles. It uses the API from [TTE Omeka Project Plugin](../tte-omeka-project/README.md) to get the term data from Omeka.
- Omeka Text List: A dynamic block which generates texts in colored tiles. It accepts an optional Omeka query to
  refine the results displayed.
- Tile Collection: displays a collection of tiles.
- Tile: creates a colored tile. It can only be created within the context of a 'Tile Collection' block.
- Carousel: creates an image carousel.

## Build

To re-build the plugin from source, run

`npm run build`

To watch the build process from source, run

`npm run start`
