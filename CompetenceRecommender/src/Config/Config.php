<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

namespace feldbusl\Plugins\CompetenceRecommender\Config;

use feldbusl\Plugins\CompetenceRecommender\Utils\CompetenceRecommenderTrait;
use ilCompetenceRecommenderPlugin;
use srag\ActiveRecordConfig\CompetenceRecommender\ActiveRecordConfig;

/**
 * Class Config
 *
 * Generated by srag\PluginGenerator v0.9.7
 *
 * @package feldbusl\Plugins\CompetenceRecommender\Config
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Leonie Feldbusch <feldbusl@informatik.uni-freiburg.de>
 */
class Config extends ActiveRecordConfig {

	use CompetenceRecommenderTrait;
	const TABLE_NAME = "ui_uihk_comprec_config";
	const PLUGIN_CLASS_NAME = ilCompetenceRecommenderPlugin::class;
	const KEY_SOME = "some";
	/**
	 * @var array
	 */
	protected static $fields = [
		self::KEY_SOME => self::TYPE_STRING
	];
	// TODO: Implement Config
}