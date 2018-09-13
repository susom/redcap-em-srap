<?php
namespace Stanford\sRAP;
/** @var \Stanford\sRAP\sRAP $module */


class displayTable
{

    public function renderTable($tag, $header=array(), $results, $record_id, $border=false, $collapse=false)
    {
        $grid = "";

        // If the table should be collapsed initially, add collapse
        if ($collapse) {
            $grid .= '<div class="collapse" id="' . $tag . '">';
        }

        //Render table
        if ($border) {
            $grid .= '<table id="' . $tag . '" class="display" style="width: 100%;" border="2px">';
        } else {
            $grid .= '<table id="' . $tag . '" class="display" style="width: 100%;" >';
        }
        $grid .= '<a class="btn-sm btn-outline-warning" data-toggle="modal" data-target="#edit' . $tag. '" data-record="'. $record_id . '" data-instance="">Create new ' . substr($tag, 0, strlen($tag)-1) . '</a><br><br>';
        if (!empty($results)) {
            $grid .= $this->renderHeaderRow($header);
            $grid .= $this->renderTableRows($results, $record_id, $tag);
        }
        $grid .= '</table>';

        if ($collapse) {
            $grid .= "</div>";
        }
        return $grid;
    }

    private function renderHeaderRow($header = array())
    {
        $row = '<thead><tr>';
        foreach ($header as $col_key => $this_col) {
            $row .= '<th>' . $this_col . '</th>';
        }

        // Add an extra column for the Edit and Delete buttons
        $row .= '<th></th>';
        $row .= '</tr><thead>';
        return $row;
    }

    private function renderTableRows($row_data = array(), $record_id, $tag)
    {
        $rows = '';

        foreach ($row_data as $row_key => $this_row) {
            $rows .= '<tr>';
            foreach ($this_row as $col_key => $this_col) {
                $rows .= '<td>' . $this_col . '</td>';
            }

            // Add an edit and delete button
            $rows .= '<td align="right"><a class="btn-sm" data-toggle="modal" data-target="#edit' . $tag. '" data-record="'. $record_id . '" data-instance="' . $this_row["id"] . '"><img src="' . APP_PATH_IMAGES . 'pencil.png"/></a>&nbsp&nbsp';
            $rows .= '<a class="btn-sm"><img src="' . APP_PATH_IMAGES . 'cross.png"/></button></td>';

            // End row
            $rows .= '</tr>';
        }

        return $rows;
    }

}


