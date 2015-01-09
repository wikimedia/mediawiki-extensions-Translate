<?php

/**
 * Message groups are usually configured in YAML, though the actual storage format does not matter,
 * because they are parsed to PHP arrays anyway. The configuration consists of sections, and in some
 * section there is key 'class' which defines the class implementing that part of behavior. These
 * classes can take custom parameters, so in essense our configuration format is open-ended. To
 * implement proper validation, those classes can extend the schema runtime by implemeting this
 * interface. Validation is implemented with the MetaYaml library.
 *
 * Because neither is_a nor instanceof accept class names, validation code will check directly
 * whether this method exists, whether the class implements the interface or not.
 *
 * @see https://github.com/romaricdrigon/MetaYaml
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration
 * @since 2014.01
 */
interface MetaYamlSchemaExtender {
	/**
	 * Return a data structure that will be merged with the base schema. It is not possible to remove
	 * things.
	 * @return array
	 */
	public static function getExtraSchema();
}
