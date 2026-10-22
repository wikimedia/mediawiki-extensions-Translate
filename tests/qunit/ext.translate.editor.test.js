/*!
 * Tests for access keys in ext.translate.editor.
 *
 * @license GPL-2.0-or-later
 */

( function () {
	'use strict';

	QUnit.module( 'ext.translate.editor', QUnit.newMwEnvironment( {
		beforeEach: function () {
			const $trigger = $( '<div>' ).addClass( 'tux-message' ).appendTo( '#qunit-fixture' );
			this.editor = Object.create( mw.translate.editor );
			this.editor.message = { group: 'test-group', title: 'MediaWiki:Test/fi' };
			this.editor.$editTrigger = $trigger;
			this.editor.$messageItem = $( '<div>' ).addClass( 'message' ).appendTo( $trigger );
			this.editor.$editor = $( '<div>' )
				.addClass( 'tux-message-editor hide' )
				.append(
					$( '<div>' ).addClass( 'editcolumn seven' ).append(
						$( '<textarea>' ),
						$( '<button>' ).addClass( 'tux-editor-save-button' )
					),
					$( '<div>' ).addClass( 'infocolumn-block five' ).append(
						$( '<div>' ).addClass( 'message-desc-viewer' ),
						$( '<div>' ).addClass( 'message-desc-editor hide' ).append(
							$( '<textarea>' ).addClass( 'tux-textarea-documentation' ),
							$( '<button>' ).addClass( 'tux-editor-savedoc-button' )
						)
					)
				)
				.appendTo( $trigger );
			this.sandbox.stub( this.editor, 'resizeInsertables' );
			this.sandbox.stub( this.editor, 'logTranslationSuggestion' );
		}
	} ) );

	function getSaveAccessKeyOwners( $editor ) {
		return $editor.find( '[accesskey="s"]' ).get().map( ( element ) => element.className );
	}

	QUnit.test( 'Save access key follows the documentation editor', function ( assert ) {
		const editor = this.editor;

		editor.show();
		assert.deepEqual( getSaveAccessKeyOwners( editor.$editor ), [ 'tux-editor-save-button' ],
			'Translation is saved when the documentation editor is closed' );

		editor.showDocumentationEditor();
		assert.deepEqual( getSaveAccessKeyOwners( editor.$editor ), [ 'tux-editor-savedoc-button' ],
			'Documentation is saved while the documentation editor is open' );

		editor.hideDocumentationEditor();
		assert.deepEqual( getSaveAccessKeyOwners( editor.$editor ), [ 'tux-editor-save-button' ],
			'Translation is saved again after closing the documentation editor' );
	} );

	QUnit.test( 'Save access key when the editor is hidden and shown again', function ( assert ) {
		const editor = this.editor;

		editor.show();
		editor.showDocumentationEditor();
		editor.hide();
		assert.deepEqual( getSaveAccessKeyOwners( editor.$editor ), [],
			'No access keys are left on a hidden editor' );

		editor.show();
		assert.deepEqual( getSaveAccessKeyOwners( editor.$editor ), [ 'tux-editor-savedoc-button' ],
			'Documentation is saved when the editor is reopened with the documentation editor open' );

		// Documentation is saved asynchronously, the editor may be hidden by the time it is done
		editor.hide();
		editor.hideDocumentationEditor();
		assert.deepEqual( getSaveAccessKeyOwners( editor.$editor ), [],
			'Closing the documentation editor does not add access keys to a hidden editor' );
	} );
}() );
