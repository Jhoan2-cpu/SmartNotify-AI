#!/usr/bin/env node

/**
 * Create plugin ZIP file for distribution
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const pluginSlug = 'smartnotify-ai';
const version = '1.0.0';
const outputFile = `${pluginSlug}-${version}.zip`;

// Files and directories to exclude
const excludePatterns = [
    'node_modules',
    'vendor',
    '.git',
    '.github',
    '.vscode',
    '.idea',
    'tests',
    'scripts',
    '.gitignore',
    '.gitattributes',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'tailwind.config.js',
    'phpunit.xml',
    '*.zip',
    '*.log',
    '.DS_Store',
    'Thumbs.db',
];

console.log(`Creating ${outputFile}...`);

try {
    // Remove existing zip if exists
    if (fs.existsSync(outputFile)) {
        fs.unlinkSync(outputFile);
        console.log('Removed existing ZIP file');
    }

    // Build exclude parameters
    const excludeParams = excludePatterns
        .map(pattern => `-x "*/${pattern}/*" -x "${pattern}"`)
        .join(' ');

    // Create zip
    const command = `zip -r ${outputFile} . ${excludeParams}`;
    execSync(command, { stdio: 'inherit' });

    console.log(`\n✅ Successfully created ${outputFile}`);

    // Get file size
    const stats = fs.statSync(outputFile);
    const fileSizeInMB = (stats.size / (1024 * 1024)).toFixed(2);
    console.log(`📦 File size: ${fileSizeInMB} MB`);

} catch (error) {
    console.error('❌ Error creating ZIP:', error.message);
    process.exit(1);
}
