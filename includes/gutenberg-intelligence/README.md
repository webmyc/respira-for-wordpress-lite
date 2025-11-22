# Gutenberg Intelligence

**Gutenberg Intelligence** is an optional addon for Respira for WordPress that provides comprehensive, production-ready support for the WordPress Block Editor (Gutenberg).

## Overview

Gutenberg Intelligence enhances the core Respira plugin with:

- **Full Block Support**: Dynamic detection and support for all registered blocks
- **Complete Schemas**: Attribute definitions and validation for every block
- **Layout Validation**: Prevents invalid structures before injection
- **Block Patterns**: Pre-built templates for common Gutenberg layouts
- **AI Intelligence**: Enhanced context and prompts for AI assistants

## Installation

Gutenberg Intelligence is loaded automatically if the `gutenberg-intelligence/` folder exists in the plugin's `includes/` directory.

The core Respira plugin works without Gutenberg Intelligence, providing basic Gutenberg support. When Gutenberg Intelligence is present, it automatically enhances the Gutenberg builder integration.

## Files

- `class-gutenberg-intelligence-loader.php` - Main loader that initializes Gutenberg Intelligence
- `class-gutenberg-block-registry.php` - Dynamic block detection and cataloging
- `class-gutenberg-block-schema.php` - Complete attribute schemas for all blocks
- `class-gutenberg-validator.php` - Layout validation before injection
- `gutenberg-patterns.php` - Layout pattern library

## Features

### Dynamic Block Detection

Gutenberg Intelligence detects all available blocks using:
1. WordPress's `WP_Block_Type_Registry`
2. Block attributes extraction
3. Block supports detection
4. Inner blocks support tracking

### Complete Block Schemas

Every block has a complete schema defining:
- All possible attributes
- Attribute types and formats
- Default values
- Example values
- Validation rules

### Layout Validation

- Pre-injection validation
- Block structure validation
- Attribute format validation
- Inner blocks validation
- Error reporting

### Block Patterns

Common Gutenberg layout patterns:
- Hero sections
- Feature sections
- Call-to-action sections
- And more

## Usage

Gutenberg Intelligence is automatically loaded when available. The `Respira_Builder_Gutenberg` class automatically uses enhanced features when Gutenberg Intelligence is present.

## API

### Get All Blocks

```php
$blocks = Respira_Gutenberg_Block_Registry::get_all_blocks();
```

### Get Block Schema

```php
$schema = new Respira_Gutenberg_Block_Schema();
$block_schema = $schema->get_builder_schema( array( 'core/paragraph', 'core/heading' ) );
```

### Validate Layout

```php
$validator = new Respira_Gutenberg_Validator();
$result = $validator->validate_layout( $content );
if ( ! $result['valid'] ) {
    // Handle errors
    $errors = $result['errors'];
}
```

### Get Patterns

```php
$patterns = respira_get_gutenberg_patterns();
```

