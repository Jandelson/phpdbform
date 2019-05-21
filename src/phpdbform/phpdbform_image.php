<?php
/**************************************
 * phpdbform_image                    *
 **************************************
 * Image control                      *
 * Saves a image into the database    *
 *                                    *
 * Paulo Assis <paulo@phpdbform.com>  *
 * 2002 - 11 - 18                     *
 **************************************/

namespace phpdbform;

class phpdbform_image extends phpdbform_field
{
    public function __construct($form_name, $field, $title, $size = 0)
    {
        $this->form_name = $form_name;
        $this->field = $field;
        $this->title = $title;
        $this->size = $size;
        $this->maxlength = 0;
        $this->key = $this->form_name . "_" . $this->field;
        $this->cssclass = "field_textbox";
        $this->updatable = false;
    }

    public function getString()
    {
        if (strlen($this->onblur)) {
            $javascript = "onblur=\"{$this->onblur}\"";
        } else {
            $javascript="";
        }
        if (!empty($this->title)) {
            $title = $this->title."<br>";
        } else {
            $title = "";
        }
        return $title."<input type=\"file\" class=\"{$this->cssclass}\" name=\"{$this->key}\" size=20 $javascript {$this->tag_extra}>\n";
    }

    public function process()
    {
        if (isset($_FILES[$this->key]["name"]) && !empty($_FILES[$this->key]["name"])) {
            if ($this->size > 0) {
                if ($this->size < $_FILES[$this->key]["size"]) {
                    //				print "size error";
                    return;
                }
            }
            $imsize= getimagesize($_FILES[$this->key]["tmp_name"]);
            // 0 - width; 1 - height
            // 2 - Image Type: 1 = GIF, 2 = JPG, 3 = PNG
            if ($imsize[2] < 1 || $imsize[2] > 3) {
                //				print "image type not supported";
                return;
            }
            $fp = fopen($_FILES[$this->key]["tmp_name"], "rb");
            if ($fp) {
                $this->value = fread($fp, $_FILES[$this->key]["size"]);
                fclose($fp);
                $this->updatable = true;
            } else {
                //				print "Error opening file";
                return;
            }
        }
    }
}
