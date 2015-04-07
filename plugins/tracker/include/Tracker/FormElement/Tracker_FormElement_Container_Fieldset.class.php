<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('common/include/Error.class.php');


class Tracker_FormElement_Container_Fieldset extends Tracker_FormElement_Container {

    /**
     * Process the request
     *
     * @param Tracker_IDisplayTrackerLayout  $layout          Displays the page header and footer
     * @param Codendi_Request                $request         The data coming from the user
     * @param PFUser                         $current_user    The user who mades the request
     *
     * @return void
     */
    public function process(Tracker_IDisplayTrackerLayout $layout, $request, $current_user) {
        switch ($request->get('func')) {
            case 'toggle-collapse':
                $current_user = $request->getCurrentUser();
                $current_user->togglePreference('fieldset_'. $this->getId(), 1, 0);
                break;
            default:
                parent::process($layout, $request, $current_user);
        }
    }

    protected function fetchArtifactPrefix() {
        $hp           = Codendi_HTMLPurifier::instance();
        $current_user = UserManager::instance()->getCurrentUser();
        $always_collapsed      = '';
        $fieldset_is_collapsed = $current_user->getPreference('fieldset_'. $this->getId());
        $fieldset_is_expanded  = ! $fieldset_is_collapsed;
        if ($fieldset_is_collapsed) {
            $always_collapsed = 'active';
        }

        $html  = '';
        $html .= '<fieldset class="tracker_artifact_fieldset">';
        $html .= '<legend title="'. $hp->purify($this->getDescription(), CODENDI_PURIFIER_CONVERT_HTML) .'" 
                          class="'. Toggler::getClassName('fieldset_'. $this->getId(), $fieldset_is_expanded, true) .'"
                          id="fieldset_'. $this->getId() .'"
                          data-id="'. $this->getId() .'">';
        $html .= '<table><tr><td class="tracker_artifact_fieldset_title">';
        $html .= $hp->purify($this->getLabel(), CODENDI_PURIFIER_CONVERT_HTML);
        $html .= '</td>';
        $html .= '<td class="tracker_artifact_fieldset_alwayscollapsed '. $always_collapsed .'">';
        if ($current_user->isLoggedIn()) {
            $html .= '<i class="icon-pushpin"></i>';
        }
        $html .= '</td></tr></table>';
        $html .= '</legend>';
        $html .= '<div class="tracker_artifact_fieldset_content">';

        return $html;
    }
    
    protected function fetchArtifactSuffix() {
        $html = '</div>';
        $html .= '</fieldset>';
        return $html;
    }
    
    protected function fetchArtifactReadOnlyPrefix() {
        return $this->fetchArtifactPrefix();
    }

    protected function fetchArtifactReadOnlySuffix() {
        return $this->fetchArtifactSuffix();
    }

    protected function fetchMailArtifactPrefix($format) {
        $label = $this->getLabel();
        if ($format == 'text') {
            return $label . PHP_EOL . str_pad('', strlen($label), '-') . PHP_EOL;
        } else {
            $purifier = Codendi_HTMLPurifier::instance();
            return '
                <tr><td colspan="2">&nbsp;</td></tr>
                <tr style="color: #444444; background-color: #F6F6F6;">
                    <td align="left" colspan="2">
                        <h3>'. $purifier->purify($label) .'</h3>
                    </td>
                </tr>';
        }
    }
    
    protected function fetchMailArtifactSuffix($format) {
        if ($format == 'text') {
            return PHP_EOL;
        } else {
            return '';
        }
    }
    
    public function fetchAdmin($tracker) {
        $html = '';
        $hp = Codendi_HTMLPurifier::instance();
        $html .= '<fieldset class="tracker-admin-container tracker-admin-fieldset" id="tracker-admin-formElements_'. $this->id .'"><legend title="'. $hp->purify($this->getDescription(), CODENDI_PURIFIER_CONVERT_HTML) .'"><label>';
        $html .= $hp->purify($this->getLabel(), CODENDI_PURIFIER_CONVERT_HTML);
        $html .= '</label><span class="tracker-admin-field-controls">';
        $html .= '<a class="edit-field" href="'. $this->getAdminEditUrl() .'">'. $GLOBALS['HTML']->getImage('ic/edit.png', array('alt' => 'edit')) .'</a> ';

        if ($this->canBeRemovedFromUsage()) {
            $html .= '<a href="?'. http_build_query(array(
                'tracker'  => $this->tracker_id,
                'func'     => 'admin-formElement-remove',
                'formElement' => $this->id,
            )) .'">'. $GLOBALS['HTML']->getImage('ic/cross.png', array('alt' => 'remove')) .'</a>';
        } else {
            $cannot_remove_message = $this->getCannotRemoveMessage();
            $html .= '<span style="color:gray;" title="'. $cannot_remove_message .'">';
            $html .= $GLOBALS['HTML']->getImage('ic/cross-disabled.png', array('alt' => 'remove'));
            $html .= '</span>';
        }
        $html .= '</span>';
        $html .= '</legend>';
        $content = array();
        foreach($this->getFormElements() as $formElement) {
            $content[] = $formElement->fetchAdmin($tracker);
        }
        $html .= implode('', $content);
        $html .= '</fieldset>';
        return $html;
    }
    
    /**
     * getLabel - the label of this Tracker_FormElement_FieldSet
     * The tracker label can be internationalized.
     * To do this, fill the name field with the ad-hoc format.
     *
     * @return string label, the name if the name is not internationalized, or the localized text if so
     */
    function getLabel() {
        global $Language;
        if ($this->isLabelMustBeLocalized()) {
            return $Language->getText('plugin_tracker_common_fieldset', $this->label);
        } else {
            return $this->label;
        }
    }
    
    /**
     * getDescriptionText - the text of the description of this Tracker_FormElement_FieldSet
     * The tracker descripiton can be internationalized.
     * To do this, fill the description field with the ad-hoc format.
     *
     * @return string description, the description text if the description is not internationalized, or the localized text if so
     */
    function getDescriptionText() {
        global $Language;
        if ($this->isDescriptionMustBeLocalized()) {
            return $Language->getText('plugin_tracker_common_fieldset', $this->description);
        } else {
            return $this->description;
        }
    }
    
    /**
     * Returns if the fieldset name must be localized or not.
     * The field set name must be localized if the name looks like fieldset_{$fieldset_id}_lbl_key
     *
     * @return true if the fieldset name must be localized, false otherwise.
     */
    function isLabelMustBeLocalized() {
        $pattern = "fieldset_(.*)_lbl_key";
        return ereg($pattern, $this->label);
    }
    
    /**
     * Returns if the fieldset description must be localized or not.
     * The field set description must be localized if the name looks like fieldset_{$fieldset_id}_desc_key
     *
     * @return true if the fieldset description must be localized, false otherwise.
     */
    function isDescriptionMustBeLocalized() {
        $pattern = "fieldset_(.*)_desc_key";
        return ereg($pattern, $this->description);
    }
    
    /**
     * @return the label of the field (mainly used in admin part)
     */
    public static function getFactoryLabel() {
        return $GLOBALS['Language']->getText('plugin_tracker_formelement_admin','fieldset');
    }
    
    /**
     * @return the description of the field (mainly used in admin part)
     */
    public static function getFactoryDescription() {
        return $GLOBALS['Language']->getText('plugin_tracker_formelement_admin','fieldset_description');
    }
    
    /**
     * @return the path to the icon
     */
    public static function getFactoryIconUseIt() {
        return $GLOBALS['HTML']->getImagePath('ic/application-form.png');
    }
    
    /**
     * @return the path to the icon
     */
    public static function getFactoryIconCreate() {
        return $GLOBALS['HTML']->getImagePath('ic/application-form--plus.png');
    }
    
    /**
     * Display the html field in the admin ui
     * @return string html
     */
    protected function fetchAdminFormElement() {
        $html = '';
        return $html;
    }
    
    /**
     * getID - get this Tracker_FormElement_FieldSet ID.
     *
     * @return int The id.
     */
    function getID() {
        return $this->id;
    }
}

?>
