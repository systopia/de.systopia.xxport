<?php
/*-------------------------------------------------------+
| SYSTOPIA EXTENSIBLE EXPORT EXTENSION                   |
| Copyright (C) 2018 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Xportx_ExtensionUtil as E;

/**
 * Provides contact base data
 */
class CRM_Xportx_Module_Membership extends CRM_Xportx_Module
{

    /**
     * This module can do with any base_table
     * (as long as it has a contact_id column)
     */
    public function forEntity()
    {
        return 'Entity';
    }

    /**
     * Get this module's preferred alias.
     * Must be all lowercase chars: [a-z]+
     */
    public function getPreferredAlias()
    {
        return 'membership';
    }

    /**
     * add this module's joins clauses to the list
     * they can only refer to the main contact table
     * "contact" or other joins from within the module
     */
    public function addJoins(&$joins)
    {
        // join membership table (with parameters)
        $contact_id       = $this->getContactIdExpression();
        $membership_alias = $this->getAlias('membership');
        $membership_join  = "LEFT JOIN civicrm_membership {$membership_alias} ON {$membership_alias}.contact_id = {$contact_id}";
        if (!empty($this->config['params']['membership_status_ids'])) {
            $status_ids      = implode(',', $this->config['params']['membership_status_ids']);
            $membership_join .= " AND {$membership_alias}.status_id IN ({$status_ids})";
        }
        if (!empty($this->config['params']['membership_type_ids'])) {
            $status_ids      = implode(',', $this->config['params']['membership_type_ids']);
            $membership_join .= " AND {$membership_alias}.membership_type_id IN ({$status_ids})";
        }
        $joins[] = $membership_join;

        // now add custom group joins (if any)
        $this->addCustomFieldJoins($joins, $membership_alias);
    }

    /**
     * add this module's select clauses to the list
     * they can only refer to the main contact table
     * "contact" or this module's joins
     */
    public function addSelects(&$selects)
    {
        $contact_alias = $this->getAlias('contact');
        $value_prefix  = $this->getValuePrefix();
        $custom_groups = $this->getCustomGroups();

        foreach ($this->config['fields'] as $field_spec) {
            $field_name = $field_spec['key'];

            // check if this is a custom field
            if ($this->isCustomField($field_name)) {
                // this is a custom field, use default code
                $this->addCustomFieldSelect($selects, $field_name);
            } else {
                // this is a base field
                switch ($field_name) {
                    // process exeptions...
//          case 'XXX':
//            $prefix_alias = $this->getAlias('prefix');
//            $selects[] = "{$prefix_alias}.label AS {$value_prefix}prefix";
//            break;

                    default:
                        // the default ist a column from the contact table
                        $selects[] = "{$contact_alias}.{$field_name} AS {$value_prefix}{$field_name}";
                        break;
                }
            }
        }
    }
}
