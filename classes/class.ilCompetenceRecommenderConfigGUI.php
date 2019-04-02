<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/docs/LICENSE */

require_once __DIR__ . "/../vendor/autoload.php";
include_once("GUI/class.ilCompetenceRecommenderConfigTableGUI.php");
include_once("class.ilCompetenceRecommenderSettings.php");

/**
 * Class ilCompetenceRecommenderConfigGUI
 *
 * Generated by srag\PluginGenerator v0.9.7
 *
 * @author Leonie Feldbusch <feldbusl@informatik.uni-freiburg.de>
 *
 * @ilCtrl_Calls ilCompetenceRecommenderConfigGUI: ilCompetenceRecommenderConfigTableGUI
 */
class ilCompetenceRecommenderConfigGUI extends ilPluginConfigGUI {

	/**
	 * @var \ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var ilRenderer
	 */
	protected $renderer;

	/**
	 * @var ilRendererFactory
	 */
	protected $factory;

	/**
	 * Constructor of the class ilDistributorTrainingsConfigGUI.
	 *
	 * @return 	void
	 */
	public function __construct()
	{
		global $DIC;

		$this->tpl = $DIC['tpl'];
		$this->lng = $DIC['lng'];
		$this->ctrl = $DIC->ctrl();
		$this->db = $DIC->database();
		$this->user = $DIC->user();
		$this->renderer = $DIC->ui()->renderer();
		$this->factory = $DIC->ui()->factory();
	}

	/**
	 * Delegate incoming comands.
	 *
	 * @param 	string 	$cmd
	 * @return 	void
	 */
	public function performCommand($cmd)
	{
		$cmd = $this->ctrl->getCmd("configure");
		switch ($cmd) {
			case "configure":
				$this->showConfig();
				break;
			case "set_init_obj":
				$this->repobjmodal();
				break;
			case "save_dropout":
				$this->saveDropout();
				break;
			case "activate_profile":
				$this->saveProfile();
				break;
			case "save_init_obj":
				$this->saveResource();
				break;
			default:
				throw new Exception("ilCompetenceRecommenderConfigGUI: Unknown command: ".$cmd);
				break;
		}
	}

	private function profiles() {
		$result = $this->db->query("SELECT id, title FROM skl_profile");
		$profiles = $this->db->fetchAll($result);
		$profileArray = array();
		foreach ($profiles as $profile) {
			$profileArray[$profile["id"]] = array("id" => $profile["id"], "title" => $profile["title"]);
		}
		return $profileArray;
	}

	private function saveDropout() {
		$value = $_POST["dropout_input"];
		if (is_numeric($value) && $value >= 0) {
			$save_settings = new ilCompetenceRecommenderSettings();
			$save_settings->set("dropout_input", strval(floor($value)));
			ilUtil::sendInfo($this->lng->txt("ui_uihk_comprec_dropout_save"));
		} else {
			ilUtil::sendFailure($this->lng->txt("ui_uihk_comprec_dropout_failure"));
		}
		$this->showConfig();
	}

	private function saveProfile() {
		$selected_profile = $_GET["profile_id"];
		$save_settings = new ilCompetenceRecommenderSettings();
		if ($save_settings->get("checked_profile_".$selected_profile) != $selected_profile) {
			$save_settings->set("checked_profile_" . $selected_profile, $selected_profile);
			ilUtil::sendSuccess($this->lng->txt("ui_uihk_comprec_config_saved_active"));
		} else {
			$save_settings->delete("checked_profile_".$selected_profile);
			ilUtil::sendSuccess($this->lng->txt("ui_uihk_comprec_config_saved_inactive"));
		}
		$this->showConfig();
	}

	/**
	 * Save resource for profile
	 */
	function saveResource()
	{
		$ref_id = (int) $_GET["root_id"];
		$selected_profile = $_GET["selected_profile"];
		if ($ref_id > 0 && isset($selected_profile))
		{
			$save_input = new ilCompetenceRecommenderSettings();
			$save_input->set("init_obj_".$selected_profile, $ref_id);
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		} else {
			ilUtil::sendFailure($this->lng->txt("ui_uihk_comprec_error"));
		}

		$this->showConfig();
	}

	private function showConfig() {
		$this->tpl->addJavascript("Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CompetenceRecommender/templates/ProfileSelector.js");
		$this->tpl->addJavascript("Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CompetenceRecommender/templates/InitObject.js");

		$available_profiles = $this->profiles();
		$old_data = new ilCompetenceRecommenderSettings();

		$html = "";

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('ui_uihk_comprec_config_dropout_title'));
		$form->addCommandButton("save_dropout", $this->lng->txt('ui_uihk_comprec_config_save'));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$dropout_input = new ilTextInputGUI($this->lng->txt('ui_uihk_comprec_dropout_input_label'), "dropout_input");
		$dropout_input->setInfo($this->lng->txt('ui_uihk_comprec_dropout_input_info'));
		$dropout_input->setValue($old_data->get("dropout_input"));
		$form->addItem($dropout_input);

		$html .= $form->getHTML();

		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('ui_uihk_comprec_profile_config'));

		$html .= $form->getHTML();

		$tabledata = array();
		foreach ($available_profiles as $profile) {
			$active = $old_data->get("checked_profile_".$profile["id"]);
			$active == $profile["id"] ? $profile["active"] = $this->lng->txt("ui_uihk_comprec_active") : $profile["active"] = $this->lng->txt("ui_uihk_comprec_inactive");
			$init_obj_id = ilObject::_lookupObjectId($old_data->get("init_obj_".$profile["id"]));
			$profile["init_obj"] = ilObject::_lookupTitle($init_obj_id);
			array_push($tabledata, $profile);
		}

		$table = new ilCompetenceRecommenderConfigTableGUI($this, $tabledata, "configure");

		$html .= $table->getHTML();

		// info text
		$html .= $this->lng->txt("ui_uihk_comprec_config_info");
		$this->tpl->setContent($html);
	}

	private function repobjmodal() {
		$this->tpl->setTitle($this->lng->txt("ui_uihk_comprec_init_obj_title"));

		include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");
		$initiationsobj = new ilRepositorySelectorExplorerGUI($this, "configure", ilCompetenceRecommenderConfigGUI::class, "save_init_obj", "root_id");

		$this->ctrl->setParameterByClass(ilCompetenceRecommenderConfigGUI::class, "profile_id", $_GET["profile_id"]);
		if (!$initiationsobj->handleCommand())
		{
			$button = $this->renderer->render($this->factory->button()->standard($this->lng->txt("ui_uihk_comprec_cancel"), $this->ctrl->getLinkTarget($this, "configure")));
			$this->tpl->setContent($initiationsobj->getHTML() . $button);
		}
	}
}
