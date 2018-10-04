<?php
/**
 * Created by PhpStorm.
 * User: LeeAnnY
 * Date: 8/7/2018
 * Time: 12:48 PM
 */
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */

use \REDCap;

require_once ($module->getModulePath() . "classes/RepeatingForms.php");

class sRAP_instances extends \Stanford\Utilities\RepeatingForms {

    private $pid;
    private $instrument;
    private $record_id;

    function __construct($pid, $instrument_name)
    {
        $this->pid = $pid;
        $this->instrument = $instrument_name;
        parent::__construct($pid, $instrument_name);
    }

    public function getAllInstancesFlat($record_id, $display_fields, $event_id=null) {

        $this->record_id = $record_id;
        $instances = $this->getAllInstances($record_id, $event_id);

        $flat_results = array();
        $display_results = array();
        $id = array();
        foreach($instances[$record_id] as $key => $value) {
            $id["id"] = $key;
            $display_results = array_intersect_key($value, array_flip($display_fields));
            $flat_results[] = array_merge($id, $display_results);
        }

        return array("size" => sizeof($instances[$record_id]), "data" =>$flat_results);
    }

    public function getDisplayHeaders($display_fields=null) {

        $instruments = array("id", $this->instrument);
        $ddictionary = REDCap::getDataDictionary($this->pid, "array", false, null, $instruments, false);

        $header = array();
        foreach ($display_fields as $field) {
            $header[$field] = $ddictionary[$field]["field_label"];
        }
        unset($header["id"]);
        return $header;
    }

    public function getSelectionLabels($field, $selected_value)
    {
        $data_dict = $this->getDataDictionary();
        $choices = array_map('trim', explode('|', $data_dict[$field]["select_choices_or_calculations"]));
        $selected = $choices[$selected_value];
        $label = array_map('trim', explode(',', $selected))[1];
        return $label;
    }

    public function getCheckboxesLabels($field, $data)
    {
        // Convert role in project into readable labels and not raw data
        $data_dict = $this->getDataDictionary();
        $cb_choices = array_map('trim', explode('|', $data_dict[$field]["select_choices_or_calculations"]));
        $cb_label = null;
        foreach ($cb_choices as $choice) {
            $pair = array_map('trim', explode(',', $choice));
            if ($data[$pair[0]] == "1") {
                if (is_null($cb_label)) {
                    $cb_label = $pair[1];
                } else {
                    $cb_label = $cb_label . ', ' . $pair[1];
                }
            }
        }
        return $cb_label;
    }


    public function renderTable($header=array(), $data, $new_button_label)
    {
        $grid = "";

        //Render table
        $grid .= '<div class="table">';
        $grid .= '<table class="table table-striped table-bordered" cellspacing="2" width="100%">';

        $grid .= '<a class="btn-sm" data-toggle="modal" data-target="#edit' . $this->instrument . '" data-record="' . $this->record_id . '" data-instance="" data-instrument="' . $this->instrument . '">New ' . $new_button_label;
        $grid .= '  <i class="far fa-plus-square"></i>';
        $grid .= '</a><br><br>';
        if (!empty($data)) {
            $grid .= $this->renderHeaderRow($header);
            $grid .= $this->renderTableRows($header, $data);
        }
        $grid .= '</table></div>';

        return $grid;
    }

    private function renderHeaderRow($header = array())
    {
        $row = '<thead><tr>';

        foreach ($header as $col_key => $this_col) {
            $row .= '<th class="th-sm">' . $this_col;
            $row .= '<i class="fa fa-sort float-right" aria-hidden="true"></i>';
            $row .= '</th>';
        }

        // Add an extra column for the Edit and Delete buttons
        $row .= '<th class="th-sm">Edit/Delete';
        $row .= '<i class="fa fa-sort float-right" aria-hidden="true"></i>';
        $row .= '</th>';


        $row .= '</tr></thead>';
        return $row;
    }

    private function renderTableRows($header, $data = array())
    {
        $rows = '<tbody>';

        foreach ($data as $row_key => $this_row) {
            $rows .= '<tr>';

            foreach($header as $col_key => $col_val) {
                $rows .= '<td>' . $this_row[$col_key] . '</td>';
            }

            // Add an edit and delete button
            $rows .= '<td>';
            $rows .= '<a class="btn-sm" data-toggle="modal" data-target="#edit' . $this->instrument. '" data-record="'. $this->record_id . '" data-instance="' . $this_row["id"] . '" data-instrument="' . $this->instrument . '">';
            $rows .= '<i class="far fa-edit"></i>';
            $rows .= '</a>&nbsp&nbsp';
            $rows .= '<a class="btn-sm" data-toggle="modal" data-target="#delete_instance" data-record="'. $this->record_id . '" data-instance="' . $this_row["id"] . '" data-instrument="' . $this->instrument . '">';
            $rows .= '<i class="far fa-trash-alt"></i>';
            $rows .= '</td>';

            // End row
            $rows .= '</tr>';
        }

        $rows .= '</tbody>';

        return $rows;
    }

}
