<?php
/**************************************
 * phpdbform_date                     *
 **************************************
 * Textbox control with support for   *
 * dates conversion to/from sql       *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2001 - 02 - 06                     *
 **************************************/

namespace phpdbform;

class phpdbform_date extends phpdbform_field
{
    public $dateformat;
    public $tipodata=1;

    public function __construct($form_name, $field, $title, $dateformat)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        if ($dateformat != "fmtUS" && $dateformat != "fmtEUR" && $dateformat != "fmtSQL") {
            $dateformat = "fmtSQL";
        }
        $this->dateformat = $dateformat;
        // giving some extra space at the control
        $this->size = 12;
        $this->maxlength = 10;
        $this->key = $this->form_name . "_" . $this->field;
        $this->cssclass = "field_textbox";
    }

    public function getString()
    {
        $tDate = "";
        //10/09/2018 16:37:21 marcelo e gilmar - para tratar quando o  campo for datetime
        if (strlen($this->value) >= 10) {
            if ($this->dateformat == "fmtUS") {
                $tDate = substr($this->value, 5, 2) . "/"
                        .substr($this->value, 8, 2) . "/"
                        .substr($this->value, 0, 4);
            } elseif ($this->dateformat == "fmtEUR") {
                $tDate = substr($this->value, 8, 2) . "/"
                        .substr($this->value, 5, 2) . "/"
                        .substr($this->value, 0, 4);
            } else {
                $tDate = $this->value;
            }
        }
        if (strlen($this->onblur)) {
            $javascript = "onblur=\"{$this->onblur}\"";
        } else {
            $javascript="";
        }
        if (!empty($this->title)) {
            $ret = $this->title."<br>";
        } else {
            $ret = "";
        }
        return $ret."<input type=text class=\"{$this->cssclass}\" id=\"$this->key\" name=\"$this->key\" size=$this->size maxlength=$this->maxlength value=\"".htmlspecialchars($tDate)."\" $javascript {$this->tag_extra}>\n";
    }

    public function process()
    {
        $tDate = "";
        if (isset($_POST[$this->key])) {
            $tDate = $_POST[$this->key];
        }
        if (strlen($tDate) == 10) {
            if ($this->dateformat == "fmtUS") {
                $this->value = substr($tDate, 6, 4) . "-"
                              .substr($tDate, 0, 2) . "-"
                              .substr($tDate, 3, 2);
            } elseif ($this->dateformat == "fmtEUR") {
                $this->value = substr($tDate, 6, 4) . "-"
                              .substr($tDate, 3, 2) . "-"
                              .substr($tDate, 0, 2);
            } else {
                $this->value = $tDate;
            }
        }
    }
}
