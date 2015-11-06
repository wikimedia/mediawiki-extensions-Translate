/*jshint node:true */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-jsonlint' );
	/*grunt.loadNpmTasks( 'grunt-banana-checker' );*/

	grunt.initConfig( {
		jshint: {
			options: {
				jshintrc: true
			},
			all: [
				'**/*.js',
				'!node_modules/**'
			]
		},
		/* banana: {
			all: [
				'i18n/api',
				'i18n/core',
				'i18n/pagetranslation',
				'i18n/sandbox',
				'i18n/search'
			]
		},
		*/
		jsonlint: {
			all: [
				'**/*.json',
				'!node_modules/**'
			]
		}
	} );

	grunt.registerTask( 'test', [ 'jshint', 'jsonlint' /*'banana'*/ ] );
	grunt.registerTask( 'default', 'test' );
};
