/* eslint-env node */
module.exports = function ( grunt ) {
	'use strict';

	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			all: [
				'**/*.js',
				'!node_modules/**',
				'!extensions/**',
				'!resources/js/jquery.autosize.js',
				'!vendor/**'
			]
		},
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**',
				'!extensions/**',
				'!vendor/**'
			]
		},
		stylelint: {
			all: [
				'**/*.css',
				'**/*.less',
				'!node_modules/**',
				'!extensions/**',
				'!vendor/**'
			]
		},
		banana: {
			all: [
				'i18n/api',
				'i18n/core',
				'i18n/pagetranslation',
				'i18n/sandbox',
				'i18n/search'
			]
		}
	} );

	grunt.registerTask( 'test', [ 'eslint', 'jsonlint', 'banana', 'stylelint' ] );
	grunt.registerTask( 'default', 'test' );
};
