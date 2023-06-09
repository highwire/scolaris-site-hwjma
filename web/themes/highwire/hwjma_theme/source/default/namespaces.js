/**
 * Share atomic concepts with Webpack, Gulp, Pattern Lab, Drupal, etc
 */
const path = require('path');

const patterns = path.resolve(__dirname, '_patterns');

module.exports = {
  // Outside of atomic concepts
  patterns,
  // This Design System
  default: path.resolve(__dirname),
  // Sub-directory design system concepts
  lib: path.resolve(__dirname, 'lib'),
  static: path.resolve(__dirname, 'static'),
  tokens: path.resolve(__dirname, 'tokens'),
  // Atomic concepts
  protons: path.resolve(patterns, '00-protons'),
  atoms: path.resolve(patterns, '01-atoms'),
  molecules: path.resolve(patterns, '02-molecules'),
  components: path.resolve(patterns, '03-components'),
  pages: path.resolve(patterns, '04-pages'),
};
