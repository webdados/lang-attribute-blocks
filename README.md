# Language Attribute for Container Blocks

A WordPress plugin that adds "lang" and "dir" attributes to Group, Columns, and Cover blocks for improved accessibility.

## Description

This plugin provides a way to ensure that any language change in the content of a page is indicated to assistive technologies at the container block level, helping a website comply with WCAG guidelines.

## Build Instructions

### Prerequisites

- Node.js (version 16 or higher)
- npm or yarn package manager
- WordPress development environment

### Development Setup

1. Clone or download the plugin to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone [repository-url] lang-attribute-blocks
   cd lang-attribute-blocks
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start development mode with hot reloading:
   ```bash
   npm run start
   ```

### Build for Production

1. Create a production build:
   ```bash
   npm run build
   ```

2. The built files will be generated in the `build/` directory:
   - `build/index.js` - Compiled JavaScript
   - `build/index.css` - Compiled CSS

### Build Scripts

- `npm run start` - Start development mode with file watching
- `npm run build` - Create production build

## Where do I report security vulnerabilities found in this plugin?
 
You can report any security bugs found in the source code of this plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/wordpress/plugin/lang-attribute-blocks/vdp). The Patchstack team will assist you with verification, CVE assignment and take care of notifying the developers of this plugin.