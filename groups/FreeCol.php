<?php

class FreeColMessageGroup extends MessageGroup {
	protected $label = 'FreeCol (open source game)';
	protected $id    = 'out-freecol';
	protected $type  = 'freecol';

	protected   $fileDir  = '__BUG__';

	public function getPath() { return $this->fileDir; }
	public function setPath( $value ) { $this->fileDir = $value; }

	protected $codeMap = array(
		'cs' => 'cs_CZ',
		'es' => 'es_ES',
		'it' => 'it_IT',
		'no' => 'nb_NO',
		'pl' => 'pl_PL',
		'sv' => 'sv_SE',
		'nl-be' => 'nl_BE',
		'en-gb' => 'en_GB',
		'en-us' => 'en_US',
		'pt'    => 'pt_PT',
		'pt-pt' => 'pt_PT',
		'pt-br' => 'pt_BR',
	);

	protected $optional = array(
		'model.foundingFather.adamSmith.birthAndDeath', 'model.foundingFather.adamSmith.name',
		'model.foundingFather.bartolomeDeLasCasas.birthAndDeath', 'model.foundingFather.bartolomeDeLasCasas.name', 'model.foundingFather.benjaminFranklin.birthAndDeath',
		'model.foundingFather.benjaminFranklin.name', 'model.foundingFather.fatherJeanDeBrebeuf.birthAndDeath', 'model.foundingFather.fatherJeanDeBrebeuf.name',
		'model.foundingFather.ferdinandMagellan.birthAndDeath', 'model.foundingFather.ferdinandMagellan.name', 'model.foundingFather.francisDrake.birthAndDeath',
		'model.foundingFather.francisDrake.name', 'model.foundingFather.franciscoDeCoronado.birthAndDeath', 'model.foundingFather.franciscoDeCoronado.name',
		'model.foundingFather.georgeWashington.birthAndDeath', 'model.foundingFather.georgeWashington.name', 'model.foundingFather.henryHudson.birthAndDeath',
		'model.foundingFather.henryHudson.name', 'model.foundingFather.hernanCortes.birthAndDeath', 'model.foundingFather.hernanCortes.name',
		'model.foundingFather.hernandoDeSoto.birthAndDeath', 'model.foundingFather.hernandoDeSoto.name', 'model.foundingFather.jacobFugger.name',
		'model.foundingFather.janDeWitt.birthAndDeath', 'model.foundingFather.janDeWitt.name', 'model.foundingFather.johnPaulJones.birthAndDeath',
		'model.foundingFather.johnPaulJones.name', 'model.foundingFather.juanDeSepulveda.birthAndDeath', 'model.foundingFather.juanDeSepulveda.name',
		'model.foundingFather.laSalle.birthAndDeath', 'model.foundingFather.laSalle.name', 'model.foundingFather.paulRevere.birthAndDeath',
		'model.foundingFather.paulRevere.name', 'model.foundingFather.peterMinuit.birthAndDeath', 'model.foundingFather.peterMinuit.name',
		'model.foundingFather.peterStuyvesant.birthAndDeath', 'model.foundingFather.peterStuyvesant.name', 'model.foundingFather.pocahontas.birthAndDeath',
		'model.foundingFather.pocahontas.name', 'model.foundingFather.simonBolivar.birthAndDeath', 'model.foundingFather.simonBolivar.name',
		'model.foundingFather.thomasJefferson.birthAndDeath', 'model.foundingFather.thomasJefferson.name', 'model.foundingFather.thomasPaine.birthAndDeath',
		'model.foundingFather.thomasPaine.name', 'model.foundingFather.williamBrewster.birthAndDeath', 'model.foundingFather.williamBrewster.name',
		'model.foundingFather.williamPenn.birthAndDeath', 'model.foundingFather.williamPenn.name', 'model.goods.goodsAmount',
		'model.model.foundingFather.jacobFugger.birthAndDeath', 'model.nation.Apache.ruler', 'model.nation.Arawak.ruler',
		'model.nation.Aztec.ruler', 'model.nation.Cherokee.ruler', 'model.nation.Dutch.ruler',
		'model.nation.English.ruler', 'model.nation.French.ruler', 'model.nation.Inca.ruler',
		'model.nation.Iroquois.ruler', 'model.nation.Portuguese.Europe', 'model.nation.Portuguese.ruler',
		'model.nation.Sioux.ruler', 'model.nation.Spanish.ruler', 'model.nation.Tupi.ruler',
		'model.nation.danish.ruler', 'model.nation.danishREF.ruler', 'model.nation.dutch.settlementName.0',
		'model.nation.dutch.settlementName.1', 'model.nation.dutch.settlementName.10', 'model.nation.dutch.settlementName.11',
		'model.nation.dutch.settlementName.12', 'model.nation.dutch.settlementName.13', 'model.nation.dutch.settlementName.14',
		'model.nation.dutch.settlementName.15', 'model.nation.dutch.settlementName.16', 'model.nation.dutch.settlementName.17',
		'model.nation.dutch.settlementName.18', 'model.nation.dutch.settlementName.19', 'model.nation.dutch.settlementName.2',
		'model.nation.dutch.settlementName.20', 'model.nation.dutch.settlementName.21', 'model.nation.dutch.settlementName.22',
		'model.nation.dutch.settlementName.23', 'model.nation.dutch.settlementName.24', 'model.nation.dutch.settlementName.25',
		'model.nation.dutch.settlementName.26', 'model.nation.dutch.settlementName.27', 'model.nation.dutch.settlementName.28',
		'model.nation.dutch.settlementName.29', 'model.nation.dutch.settlementName.3', 'model.nation.dutch.settlementName.30',
		'model.nation.dutch.settlementName.31', 'model.nation.dutch.settlementName.4', 'model.nation.dutch.settlementName.5',
		'model.nation.dutch.settlementName.6', 'model.nation.dutch.settlementName.7', 'model.nation.dutch.settlementName.8',
		'model.nation.dutch.settlementName.9', 'model.nation.dutch.region.land.1', 'model.nation.dutch.region.land.10',
		'model.nation.dutch.region.land.2', 'model.nation.dutch.region.land.3', 'model.nation.dutch.region.land.4',
		'model.nation.dutch.region.land.5', 'model.nation.dutch.region.land.6', 'model.nation.dutch.region.land.7',
		'model.nation.dutch.region.land.8', 'model.nation.dutch.region.land.9', 'model.nation.dutch.region.mountain.1',
		'model.nation.dutch.region.mountain.2', 'model.nation.dutch.region.mountain.3', 'model.nation.dutch.region.mountain.4',
		'model.nation.dutch.region.mountain.5', 'model.nation.dutch.region.mountain.6', 'model.nation.dutch.region.mountain.7',
		'model.nation.dutch.region.mountain.8', 'model.nation.dutch.region.mountain.9', 'model.nation.dutch.region.river.1',
		'model.nation.dutch.region.river.2', 'model.nation.dutch.region.river.3', 'model.nation.dutch.region.river.4',
		'model.nation.dutch.region.river.5', 'model.nation.dutch.region.river.6', 'model.nation.dutch.region.river.7',
		'model.nation.dutch.region.river.8', 'model.nation.english.settlementName.0', 'model.nation.english.settlementName.1',
		'model.nation.english.settlementName.10', 'model.nation.english.settlementName.11', 'model.nation.english.settlementName.12',
		'model.nation.english.settlementName.13', 'model.nation.english.settlementName.14', 'model.nation.english.settlementName.15',
		'model.nation.english.settlementName.16', 'model.nation.english.settlementName.17', 'model.nation.english.settlementName.18',
		'model.nation.english.settlementName.19', 'model.nation.english.settlementName.2', 'model.nation.english.settlementName.20',
		'model.nation.english.settlementName.21', 'model.nation.english.settlementName.22', 'model.nation.english.settlementName.23',
		'model.nation.english.settlementName.24', 'model.nation.english.settlementName.25', 'model.nation.english.settlementName.26',
		'model.nation.english.settlementName.27', 'model.nation.english.settlementName.28', 'model.nation.english.settlementName.29',
		'model.nation.english.settlementName.3', 'model.nation.english.settlementName.30', 'model.nation.english.settlementName.31',
		'model.nation.english.settlementName.32', 'model.nation.english.settlementName.33', 'model.nation.english.settlementName.34',
		'model.nation.english.settlementName.35', 'model.nation.english.settlementName.4', 'model.nation.english.settlementName.5',
		'model.nation.english.settlementName.6', 'model.nation.english.settlementName.7', 'model.nation.english.settlementName.8',
		'model.nation.english.settlementName.9', 'model.nation.english.region.land.1', 'model.nation.english.region.land.10',
		'model.nation.english.region.land.2', 'model.nation.english.region.land.3', 'model.nation.english.region.land.4',
		'model.nation.english.region.land.5', 'model.nation.english.region.land.6', 'model.nation.english.region.land.7',
		'model.nation.english.region.land.8', 'model.nation.english.region.land.9', 'model.nation.english.region.mountain.1',
		'model.nation.english.region.mountain.2', 'model.nation.english.region.mountain.3', 'model.nation.english.region.mountain.4',
		'model.nation.english.region.mountain.5', 'model.nation.english.region.mountain.6', 'model.nation.english.region.mountain.7',
		'model.nation.english.region.mountain.8', 'model.nation.english.region.mountain.9', 'model.nation.english.region.river.1',
		'model.nation.english.region.river.10', 'model.nation.english.region.river.2', 'model.nation.english.region.river.3',
		'model.nation.english.region.river.4', 'model.nation.english.region.river.5', 'model.nation.english.region.river.6',
		'model.nation.english.region.river.7', 'model.nation.english.region.river.8', 'model.nation.english.region.river.9',
		'model.nation.french.settlementName.0', 'model.nation.french.settlementName.1', 'model.nation.french.settlementName.10',
		'model.nation.french.settlementName.11', 'model.nation.french.settlementName.12', 'model.nation.french.settlementName.13',
		'model.nation.french.settlementName.14', 'model.nation.french.settlementName.15', 'model.nation.french.settlementName.16',
		'model.nation.french.settlementName.17', 'model.nation.french.settlementName.18', 'model.nation.french.settlementName.19',
		'model.nation.french.settlementName.2', 'model.nation.french.settlementName.20', 'model.nation.french.settlementName.21',
		'model.nation.french.settlementName.22', 'model.nation.french.settlementName.23', 'model.nation.french.settlementName.24',
		'model.nation.french.settlementName.25', 'model.nation.french.settlementName.26', 'model.nation.french.settlementName.27',
		'model.nation.french.settlementName.28', 'model.nation.french.settlementName.29', 'model.nation.french.settlementName.3',
		'model.nation.french.settlementName.30', 'model.nation.french.settlementName.31', 'model.nation.french.settlementName.32',
		'model.nation.french.settlementName.33', 'model.nation.french.settlementName.34', 'model.nation.french.settlementName.35',
		'model.nation.french.settlementName.36', 'model.nation.french.settlementName.37', 'model.nation.french.settlementName.38',
		'model.nation.french.settlementName.39', 'model.nation.french.settlementName.4', 'model.nation.french.settlementName.40',
		'model.nation.french.settlementName.41', 'model.nation.french.settlementName.42', 'model.nation.french.settlementName.43',
		'model.nation.french.settlementName.44', 'model.nation.french.settlementName.45', 'model.nation.french.settlementName.46',
		'model.nation.french.settlementName.47', 'model.nation.french.settlementName.48', 'model.nation.french.settlementName.49',
		'model.nation.french.settlementName.5', 'model.nation.french.settlementName.50', 'model.nation.french.settlementName.51',
		'model.nation.french.settlementName.52', 'model.nation.french.settlementName.53', 'model.nation.french.settlementName.54',
		'model.nation.french.settlementName.55', 'model.nation.french.settlementName.56', 'model.nation.french.settlementName.57',
		'model.nation.french.settlementName.58', 'model.nation.french.settlementName.59', 'model.nation.french.settlementName.6',
		'model.nation.french.settlementName.60', 'model.nation.french.settlementName.61', 'model.nation.french.settlementName.62',
		'model.nation.french.settlementName.63', 'model.nation.french.settlementName.64', 'model.nation.french.settlementName.65',
		'model.nation.french.settlementName.7', 'model.nation.french.settlementName.8', 'model.nation.french.settlementName.9',
		'model.nation.french.region.land.1', 'model.nation.french.region.land.2', 'model.nation.french.region.land.3',
		'model.nation.french.region.land.4', 'model.nation.french.region.land.5', 'model.nation.french.region.land.6',
		'model.nation.french.region.land.7',
		'model.nation.french.region.mountain.1', 'model.nation.french.region.mountain.10', 'model.nation.french.region.mountain.2',
		'model.nation.french.region.mountain.3', 'model.nation.french.region.mountain.4', 'model.nation.french.region.mountain.5',
		'model.nation.french.region.mountain.6', 'model.nation.french.region.mountain.7', 'model.nation.french.region.mountain.8',
		'model.nation.french.region.mountain.9', 'model.nation.french.region.river.1', 'model.nation.french.region.river.10',
		'model.nation.french.region.river.2', 'model.nation.french.region.river.3', 'model.nation.french.region.river.4',
		'model.nation.french.region.river.5', 'model.nation.french.region.river.6', 'model.nation.french.region.river.7',
		'model.nation.french.region.river.8', 'model.nation.french.region.river.9', 'model.nation.portuguese.settlementName.0',
		'model.nation.portuguese.settlementName.1', 'model.nation.portuguese.settlementName.10', 'model.nation.portuguese.settlementName.11',
		'model.nation.portuguese.settlementName.12', 'model.nation.portuguese.settlementName.13', 'model.nation.portuguese.settlementName.14',
		'model.nation.portuguese.settlementName.15', 'model.nation.portuguese.settlementName.16', 'model.nation.portuguese.settlementName.17',
		'model.nation.portuguese.settlementName.18', 'model.nation.portuguese.settlementName.19', 'model.nation.portuguese.settlementName.2',
		'model.nation.portuguese.settlementName.20', 'model.nation.portuguese.settlementName.21', 'model.nation.portuguese.settlementName.22',
		'model.nation.portuguese.settlementName.23', 'model.nation.portuguese.settlementName.24', 'model.nation.portuguese.settlementName.25',
		'model.nation.portuguese.settlementName.26', 'model.nation.portuguese.settlementName.27', 'model.nation.portuguese.settlementName.28',
		'model.nation.portuguese.settlementName.29', 'model.nation.portuguese.settlementName.3', 'model.nation.portuguese.settlementName.4',
		'model.nation.portuguese.settlementName.5', 'model.nation.portuguese.settlementName.6', 'model.nation.portuguese.settlementName.7',
		'model.nation.portuguese.settlementName.8', 'model.nation.portuguese.settlementName.9', 'model.nation.refDutch.ruler',
		'model.nation.refEnglish.ruler', 'model.nation.refFrench.ruler', 'model.nation.refPortuguese.ruler',
		'model.nation.refSpanish.ruler', 'model.nation.russian.ruler', 'model.nation.russianREF.ruler',
		'model.nation.spanish.settlementName.0', 'model.nation.spanish.settlementName.1', 'model.nation.spanish.settlementName.10',
		'model.nation.spanish.settlementName.11', 'model.nation.spanish.settlementName.12', 'model.nation.spanish.settlementName.13',
		'model.nation.spanish.settlementName.14', 'model.nation.spanish.settlementName.15', 'model.nation.spanish.settlementName.16',
		'model.nation.spanish.settlementName.17', 'model.nation.spanish.settlementName.18', 'model.nation.spanish.settlementName.19',
		'model.nation.spanish.settlementName.2', 'model.nation.spanish.settlementName.20', 'model.nation.spanish.settlementName.21',
		'model.nation.spanish.settlementName.22', 'model.nation.spanish.settlementName.23', 'model.nation.spanish.settlementName.24',
		'model.nation.spanish.settlementName.25', 'model.nation.spanish.settlementName.26', 'model.nation.spanish.settlementName.27',
		'model.nation.spanish.settlementName.28', 'model.nation.spanish.settlementName.29', 'model.nation.spanish.settlementName.3',
		'model.nation.spanish.settlementName.30', 'model.nation.spanish.settlementName.31', 'model.nation.spanish.settlementName.32',
		'model.nation.spanish.settlementName.33', 'model.nation.spanish.settlementName.34', 'model.nation.spanish.settlementName.35',
		'model.nation.spanish.settlementName.36', 'model.nation.spanish.settlementName.37', 'model.nation.spanish.settlementName.38',
		'model.nation.spanish.settlementName.4', 'model.nation.spanish.settlementName.5', 'model.nation.spanish.settlementName.6',
		'model.nation.spanish.settlementName.7', 'model.nation.spanish.settlementName.8', 'model.nation.spanish.settlementName.9',
		'model.nation.spanish.region.land.1', 'model.nation.spanish.region.land.2', 'model.nation.spanish.region.land.3',
		'model.nation.spanish.region.land.4', 'model.nation.spanish.region.land.5', 'model.nation.spanish.region.river.1',
		'model.nation.spanish.region.river.2', 'model.nation.swedish.europe', 'model.nation.swedish.ruler',
		'model.nation.swedishREF.ruler', 'model.unit.occupation.activeNoMovesLeft',
		'shipName.3.0', 'model.nation.spanish.region.mountain.1', 'model.nation.spanish.region.mountain.2',
		'model.nation.spanish.region.mountain.3', 'model.nation.spanish.region.mountain.4', 'model.nation.spanish.region.mountain.5',
		'model.nation.spanish.region.river.3', 'model.nation.spanish.region.river.4', 'model.nation.spanish.region.river.5',
		'model.region.default', 'model.nation.danish.settlementName.0', 'model.nation.danish.settlementName.1',
		'model.nation.danish.settlementName.2',
		'model.nation.danish.settlementName.10', 'model.nation.danish.settlementName.11', 'model.nation.danish.settlementName.12',
		'model.nation.danish.settlementName.3', 'model.nation.danish.settlementName.4', 'model.nation.danish.settlementName.5',
		'model.nation.danish.settlementName.6', 'model.nation.danish.settlementName.7', 'model.nation.danish.settlementName.8',
		'model.nation.danish.settlementName.9', 'model.nation.danish.region.land.1', 'model.nation.danish.region.land.2',
		'model.nation.danish.region.land.3', 'model.nation.danish.region.land.4', 'model.nation.danish.region.land.5',
		'model.nation.danish.region.land.6', 'model.nation.danish.region.mountain.1', 'model.nation.danish.region.mountain.2',
		'model.nation.danish.region.mountain.3', 'model.nation.danish.region.mountain.4', 'model.nation.danish.region.mountain.5',
		'model.nation.danish.region.mountain.6', 'model.nation.danish.region.river.1', 'model.nation.danish.region.river.2',
		'model.nation.danish.region.river.3', 'model.nation.russian.settlementName.0', 'model.nation.russian.settlementName.1',
		'model.nation.russian.settlementName.10',
		'model.nation.russian.settlementName.11', 'model.nation.russian.settlementName.12', 'model.nation.russian.settlementName.13',
		'model.nation.russian.settlementName.14', 'model.nation.russian.settlementName.15', 'model.nation.russian.settlementName.16',
		'model.nation.russian.settlementName.17', 'model.nation.russian.settlementName.18', 'model.nation.russian.settlementName.19',
		'model.nation.russian.settlementName.2', 'model.nation.russian.settlementName.20', 'model.nation.russian.settlementName.21',
		'model.nation.russian.settlementName.22', 'model.nation.russian.settlementName.23', 'model.nation.russian.settlementName.3',
		'model.nation.russian.settlementName.4', 'model.nation.russian.settlementName.5', 'model.nation.russian.settlementName.6',
		'model.nation.russian.settlementName.7', 'model.nation.russian.settlementName.8', 'model.nation.russian.settlementName.9',
		'model.nation.russian.region.land.1', 'model.nation.russian.region.land.2', 'model.nation.russian.region.land.3',
		'model.nation.russian.region.land.4', 'model.nation.russian.region.land.5', 'model.nation.russian.region.mountain.1',
		'model.nation.russian.region.mountain.2', 'model.nation.russian.region.mountain.3', 'model.nation.russian.region.mountain.4',
		'model.nation.russian.region.mountain.5', 'model.nation.russian.region.river.1', 'model.nation.russian.region.river.2',
		'model.nation.russian.region.river.3', 'model.nation.russian.region.river.4', 'model.nation.swedish.settlementName.0',
		'model.nation.swedish.settlementName.1', 'model.nation.swedish.settlementName.10', 'model.nation.swedish.settlementName.11',
		'model.nation.swedish.settlementName.12', 'model.nation.swedish.settlementName.13', 'model.nation.swedish.settlementName.14',
		'model.nation.swedish.settlementName.15', 'model.nation.swedish.settlementName.16', 'model.nation.swedish.settlementName.17',
		'model.nation.swedish.settlementName.18', 'model.nation.swedish.settlementName.19', 'model.nation.swedish.settlementName.2',
		'model.nation.swedish.settlementName.20', 'model.nation.swedish.settlementName.21', 'model.nation.swedish.settlementName.3',
		'model.nation.swedish.settlementName.4', 'model.nation.swedish.settlementName.5', 'model.nation.swedish.settlementName.6',
		'model.nation.swedish.settlementName.7', 'model.nation.swedish.settlementName.8', 'model.nation.swedish.settlementName.9',
		'model.nation.swedish.region.land.1', 'model.nation.swedish.region.land.2', 'model.nation.swedish.region.land.3',
		'model.nation.swedish.region.river.1', 'model.nation.swedish.region.river.2', 'model.nation.swedish.region.river.3',
		'model.nation.swedish.region.river.4',
	);

	protected $ignored = array(
		'headerFont',
	);

	public function getMessageFile( $code ) {
		if ( $code == 'en' ) {
			return 'FreeColMessages.properties';
		} else {
			if ( isset( $this->codeMap[$code] ) ) {
				$code = $this->codeMap[$code];
			}
			return "FreeColMessages_$code.properties";
		}
	}

	protected function getFileLocation( $code ) {
		return $this->fileDir . '/' . $this->getMessageFile( $code );
	}

	public function getReader( $code ) {
		return new JavaFormatReader( $this->getFileLocation( $code ) );
	}

	public function getWriter() {
		return new JavaFormatWriter( $this );
	}
}
