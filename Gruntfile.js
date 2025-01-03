'use strict';

module.exports = function ( grunt ) {
	const conf = grunt.file.readJSON( 'extension.json' );

	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			options: {
				cache: true,
				fix: grunt.option( 'fix' )
			},
			all: [ '.' ]
		},
		stylelint: {
			options: {
				cache: true
			},
			all: [
				'**/*.css',
				'**/*.less',
				'!node_modules/**',
				'!vendor/**',
				'!resources/lib/**'
			]
		},
		banana: Object.assign( conf.MessagesDirs, {
			options: {
				requireLowerCase: 'initial'
			}
		} )
	} );

	grunt.registerTask( 'test', [ 'eslint', 'banana', 'stylelint' ] );
	grunt.registerTask( 'lint:js', [ 'eslint' ] );
	grunt.registerTask( 'default', 'test' );
};
