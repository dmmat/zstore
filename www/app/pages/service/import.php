<?php

namespace App\Pages\Service;

use App\Entity\Category;
use App\Entity\Customer;
use App\Entity\Item;
use App\Entity\Store;
use App\Helper as H;
use Zippy\Html\Form\AutocompleteTextInput;
use Zippy\Html\Form\CheckBox;
use Zippy\Html\Form\DropDownChoice;
use Zippy\Html\Form\Form;
use Zippy\Html\Form\TextInput;
use Zippy\Html\Label;
use Zippy\WebApplication as App;

class Import extends \App\Pages\Base
{

    public function __construct()
    {
        parent::__construct();
        if (false == \App\ACL::checkShowSer('Import')) {
            return;
        }

        //ТМЦ
        $form = $this->add(new Form("iform"));

        $form->add(new DropDownChoice("itype", array(), 0))->onChange($this, "onType");

        $form->add(new DropDownChoice("item_type", Item::getTypes(), Item::TYPE_TOVAR));

        $form->add(new DropDownChoice("store", Store::getList(), H::getDefStore()));

        $form->add(new \Zippy\Html\Form\File("filename"));
        $cols = array(0 => '-', 'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G',
            'H' => 'H', 'I' => 'I', 'J' => 'J', 'K' => 'K', 'L' => 'L', 'M' => 'M', 'N' => 'N', 'O' => 'O',
            'P' => 'P', 'R' => 'R', 'S' => 'S', 'T' => 'T');
        $form->add(new DropDownChoice("colname", $cols));
        $form->add(new DropDownChoice("colcode", $cols));
        $form->add(new DropDownChoice("colbarcode", $cols));
        $form->add(new DropDownChoice("colgr", $cols));
        $form->add(new DropDownChoice("colqty", $cols));
        $form->add(new DropDownChoice("colprice1", $cols));
        $form->add(new DropDownChoice("colprice2", $cols));
        $form->add(new DropDownChoice("colprice3", $cols));
        $form->add(new DropDownChoice("colprice4", $cols));
        $form->add(new DropDownChoice("colprice5", $cols));
        $form->add(new DropDownChoice("colcell", $cols));

        $pt = \App\Entity\Item::getPriceTypeList();

        $form->add(new Label('pricename1', $pt['price1']));
        $form->add(new Label('pricename2', $pt['price2']));
        $form->add(new Label('pricename3', $pt['price3']));
        $form->add(new Label('pricename4', $pt['price4']));
        $form->add(new Label('pricename5', $pt['price5']));

        $form->add(new DropDownChoice("colinprice", $cols));
        $form->add(new DropDownChoice("colmsr", $cols));
        $form->add(new DropDownChoice("colbrand", $cols));
        $form->add(new DropDownChoice("coldesc", $cols));
        $form->add(new CheckBox("passfirst"));
        $form->add(new CheckBox("preview"));
        $form->add(new CheckBox("checkname"));
        $form->add(new CheckBox("noshowprice"));
        $form->add(new CheckBox("noshowshop"));


        $form->onSubmit($this, "onImport");

        $this->onType($form->itype);

        //накладная
        $form = $this->add(new Form("nform"));

        $form->add(new DropDownChoice("nstore", Store::getList(), H::getDefStore()));

        $form->add(new AutocompleteTextInput("ncust"))->onText($this, 'OnAutoCustomer');
        $form->add(new \Zippy\Html\Form\File("nfilename"));

        $form->add(new DropDownChoice("ncolname", $cols));
        $form->add(new DropDownChoice("ncolcode", $cols));
        $form->add(new DropDownChoice("ncolbarcode", $cols));
        $form->add(new DropDownChoice("ncolqty", $cols));
        $form->add(new DropDownChoice("ncolprice", $cols));
        $form->add(new DropDownChoice("ncolmsr", $cols));
        $form->add(new DropDownChoice("nsdate", $cols));
        $form->add(new DropDownChoice("nsnumber", $cols));
        $form->add(new CheckBox("npassfirst"));
        $form->add(new CheckBox("npreview"));
        $form->add(new CheckBox("ncheckname"));

        $form->onSubmit($this, "onNImport");

        //контрагенты
        $form = $this->add(new Form("cform"));

        $form->add(new DropDownChoice("ctype", array(), 0));

        $form->add(new CheckBox("cpreview"));
        $form->add(new CheckBox("cpassfirst"));
        $form->add(new DropDownChoice("colcname", $cols));
        $form->add(new DropDownChoice("colphone", $cols));
        $form->add(new DropDownChoice("colemail", $cols));
        $form->add(new DropDownChoice("colcity", $cols));
        $form->add(new DropDownChoice("coladdress", $cols));
        $form->add(new \Zippy\Html\Form\File("cfilename"));

        $form->onSubmit($this, "onCImport");

        $this->_tvars['preview'] = false;
        $this->_tvars['preview2'] = false;
        $this->_tvars['preview3'] = false;
    }

    public function OnAutoCustomer($sender)
    {
        return Customer::getList($sender->getText());
    }

    public function onType($sender)
    {
        $t = $sender->getValue();

        $this->iform->colqty->setVisible($t == 1);
        $this->iform->store->setVisible($t == 1);
        $this->iform->colinprice->setVisible($t == 1);
        $this->iform->checkname->setVisible(true);
        $this->iform->item_type->setVisible(true);
        $this->iform->noshowprice->setVisible(true);
        $this->iform->noshowshop->setVisible(true);
        $this->iform->colname->setVisible(true);
        $this->iform->colbarcode->setVisible(true);
        $this->iform->colgr->setVisible(true);
        $this->iform->colmsr->setVisible(true);
        $this->iform->coldesc->setVisible(true);
        $this->iform->colqty->setVisible(true);


        if ($t == 2) {
            $this->iform->item_type->setVisible(false);
            $this->iform->checkname->setVisible(false);
            $this->iform->noshowprice->setVisible(false);
            $this->iform->noshowshop->setVisible(false);
            $this->iform->colbarcode->setVisible(false);
            $this->iform->colgr->setVisible(false);
            $this->iform->colmsr->setVisible(false);
            $this->iform->coldesc->setVisible(false);
            $this->iform->colqty->setVisible(false);
            $this->iform->colinprice->setVisible(false);

        }
    }

    public function onImport($sender)
    {
        $t = $this->iform->itype->getValue();
        $store = $this->iform->store->getValue();
        $item_type = $this->iform->item_type->getValue();

        $preview = $this->iform->preview->isChecked();
        $passfirst = $this->iform->passfirst->isChecked();

        $checkname = $this->iform->checkname->isChecked();
        $this->_tvars['preview'] = false;

        $colname = $this->iform->colname->getValue();
        $colcode = $this->iform->colcode->getValue();
        $colbarcode = $this->iform->colbarcode->getValue();
        $colgr = $this->iform->colgr->getValue();
        $colqty = $this->iform->colqty->getValue();
        $colprice1 = $this->iform->colprice1->getValue();
        $colprice2 = $this->iform->colprice2->getValue();
        $colprice3 = $this->iform->colprice3->getValue();
        $colprice4 = $this->iform->colprice4->getValue();
        $colprice5 = $this->iform->colprice5->getValue();
        $colinprice = $this->iform->colinprice->getValue();
        $colmsr = $this->iform->colmsr->getValue();
        $colbrand = $this->iform->colbrand->getValue();
        $coldesc = $this->iform->coldesc->getValue();
        if ($colname === '0' && $t != 2) {
            $this->setError('noselcolname');
            return;
        }
        if ($t == 1 && $colqty === '0') {
            $this->setError('noselcolqty');
            return;
        }
        $file = $this->iform->filename->getFile();
        if (strlen($file['tmp_name']) == 0) {

            $this->setError('noselfile');
            return;
        }

        $data = array();
        $oSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']); // Вариант и для xls и xlsX


        $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();

        for ($iRow = ($passfirst ? 2 : 1); $iRow <= $oCells->getHighestRow(); $iRow++) {

            $row = array();
            for ($iCol = 'A'; $iCol <= $oCells->getHighestColumn(); $iCol++) {
                $oCell = $oCells->get($iCol . $iRow);
                if ($oCell) {
                    $row[$iCol] = $oCell->getValue();
                }
            }
            $data[$iRow] = $row;
        }

        unset($oSpreadsheet);

        if ($preview) {

            $this->_tvars['preview'] = true;
            $this->_tvars['list'] = array();
            foreach ($data as $row) {

                $this->_tvars['list'][] = array(
                    'colname' => $row[$colname],
                    'colcode' => $row[$colcode],
                    'colbarcode' => $row[$colbarcode],
                    'colgr' => $row[$colgr],
                    'colqty' => $row[$colqty],
                    'colmsr' => $row[$colmsr],
                    'colinprice' => $row[$colinprice],
                    'colprice1' => $row[$colprice1],
                    'colprice2' => $row[$colprice2],
                    'colprice3' => $row[$colprice3],
                    'colprice4' => $row[$colprice4],
                    'colprice5' => $row[$colprice5],
                    'colbrand' => $row[$colbrand],
                    'coldesc' => $row[$coldesc]
                );
            }
            return;
        }

        $cnt = 0;
        $newitems = array();
        foreach ($data as $row) {

            $price1 = str_replace(',', '.', trim($row[$colprice1]));
            $price2 = str_replace(',', '.', trim($row[$colprice2]));
            $price3 = str_replace(',', '.', trim($row[$colprice3]));
            $price4 = str_replace(',', '.', trim($row[$colprice4]));
            $price5 = str_replace(',', '.', trim($row[$colprice5]));
            $itemcode = trim($row[$colcode]);
            $brand = trim($row[$colbrand]);

            if ($t == 2) {   //обновление  цен

                if (strlen($itemcode) == 0) {
                    continue;
                }
                if (strlen($brand) > 0) {
                    $it = Item::getFirst('item_code=' . Item::qstr($itemcode) . " and manufacturer = " . Item::qstr($brand));
                } else {
                    $it = Item::getFirst('item_code=' . Item::qstr($itemcode));
                }
                if ($it == null) {
                    continue;
                }

                if ($colprice1 != "0") {
                    $it->price1 = $price1;
                }
                if ($colprice2 != "0") {
                    $it->price2 = $price2;
                }
                if ($colprice3 != "0") {
                    $it->price3 = $price3;
                }
                if ($colprice4 != "0") {
                    $it->price4 = $price4;
                }
                if ($colprice5 != "0") {
                    $it->price5 = $price5;
                }


                $it->save();
                $cnt++;

                continue;
            }


            $catname = $row[$colgr];
            if (strlen($catname) > 0) {
                $cat = Category::getFirst('cat_name=' . Category::qstr($catname));
                if ($cat == null) {
                    $cat = new Category();
                    $cat->cat_name = $catname;
                    $cat->save();
                }
            }
            $item = null;
            $itemname = trim($row[$colname]);
            $itemcode = trim($row[$colcode]);
            $itembarcode = trim($row[$colbarcode]);
            if (strlen($itemname) > 0) {

                if (strlen($itembarcode) > 0) {
                    $item = Item::getFirst('bar_code=' . Item::qstr($itembarcode));
                } else {
                    if (strlen($itemcode) > 0) {
                        $item = Item::getFirst('item_code=' . Item::qstr($itemcode));
                    }
                }

                if ($item == null && $checkname == true) {
                    $item = Item::getFirst('itemname=' . Item::qstr($itemname));
                }


                if ($item == null) {
                    $price1 = str_replace(',', '.', trim($row[$colprice1]));
                    $price2 = str_replace(',', '.', trim($row[$colprice2]));
                    $price3 = str_replace(',', '.', trim($row[$colprice3]));
                    $price4 = str_replace(',', '.', trim($row[$colprice4]));
                    $price5 = str_replace(',', '.', trim($row[$colprice5]));
                    $inprice = str_replace(',', '.', trim($row[$colinprice]));
                    $qty = str_replace(',', '.', trim($row[$colqty]));
                    $item = new Item();
                    $item->itemname = $itemname;
                    if (strlen($row[$colcode]) > 0) {
                        $item->item_code = trim($row[$colcode]);
                    }
                    if (strlen($row[$colbarcode]) > 0) {
                        $item->bar_code = trim($row[$colbarcode]);
                    }
                    if (strlen($row[$colmsr]) > 0) {
                        $item->msr = trim($row[$colmsr]);
                    }
                    if (strlen($row[$colcell]) > 0) {
                        $item->cell = trim($row[$colcell]);
                    }
                    if (strlen($row[$colbrand]) > 0) {
                        $item->manufacturer = $row[$colbrand];
                    }
                    if (strlen(trim($row[$coldesc])) > 0) {
                        $item->description = trim($row[$coldesc]);
                    }
                    if ($price1 > 0) {
                        $item->price1 = $price1;
                    }
                    if ($price2 > 0) {
                        $item->price2 = $price2;
                    }
                    if ($price3 > 0) {
                        $item->price3 = $price3;
                    }
                    if ($price4 > 0) {
                        $item->price4 = $price4;
                    }
                    if ($price5 > 0) {
                        $item->price5 = $price5;
                    }

                    if ($inprice > 0) {
                        $item->price = $inprice;
                    }
                    if ($qty > 0) {
                        $item->quantity = $qty;
                    }
                    if ($cat->cat_id > 0) {
                        $item->cat_id = $cat->cat_id;
                    }
                    if ($item_type > 0) {
                        $item->item_type = $item_type;
                    }

                    $item->amount = $item->quantity * $item->price;

                    $item->noprice = $this->iform->noshowprice->isChecked() ? 1 : 0;
                    $item->noshop = $this->iform->noshowshop->isChecked() ? 1 : 0;

                    $item->save();
                    $cnt++;
                    if ($item->quantity > 0) {
                        $newitems[] = $item; //для склада   
                    }
                }
            }
        }
        if (count($newitems) > 0) {
            $doc = \App\Entity\Doc\Document::create('IncomeItem');
            $doc->document_number = $doc->nextNumber();
            if (strlen($doc->document_number) == 0) {
                $doc->document_number = "ПТ00001";
            }
            $doc->document_date = time();

            $amount = 0;
            $itlist = array();
            foreach ($newitems as $item) {
                $itlist[$item->item_id] = $item;
                $amount = $amount + ($item->quantity * $item->price);
            }
            $doc->packDetails('detaildata', $itlist);
            $doc->amount = H::fa($amount);
            $doc->payamount = 0;
            $doc->payed = 0;
            $doc->notes = 'Импорт с Excel';
            $doc->headerdata['store'] = $store;

            $doc->save();
            $doc->updateStatus(\App\Entity\Doc\Document::STATE_NEW);
            $doc->updateStatus(\App\Entity\Doc\Document::STATE_EXECUTED);
        }

        $this->setSuccess("imported_items", $cnt);
    }

    public function onCImport($sender)
    {
        $t = $this->cform->ctype->getValue();

        $preview = $this->cform->cpreview->isChecked();
        $passfirst = $this->cform->cpassfirst->isChecked();
        $this->_tvars['preview2'] = false;

        $colcname = $this->cform->colcname->getValue();
        $colphone = $this->cform->colphone->getValue();
        $colemail = $this->cform->colemail->getValue();
        $colcity = $this->cform->colcity->getValue();
        $coladdress = $this->cform->coladdress->getValue();

        if ($colcname === '0') {
            $this->setError('noselcolname');
            return;
        }

        $file = $this->cform->cfilename->getFile();
        if (strlen($file['tmp_name']) == 0) {
            $this->setError('noselfile');
            return;
        }


        $data = array();

        $oSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']); // Вариант и для xls и xlsX


        $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();

        for ($iRow = ($passfirst ? 2 : 1); $iRow <= $oCells->getHighestRow(); $iRow++) {

            $row = array();
            for ($iCol = 'A'; $iCol <= $oCells->getHighestColumn(); $iCol++) {
                $oCell = $oCells->get($iCol . $iRow);
                if ($oCell) {
                    $row[$iCol] = $oCell->getValue();
                }
            }
            $data[$iRow] = $row;
        }

        unset($oSpreadsheet);

        if ($preview) {

            $this->_tvars['preview2'] = true;
            $this->_tvars['list2'] = array();
            foreach ($data as $row) {

                $this->_tvars['list2'][] = array(
                    'colname' => $row[$colcname],
                    'colphone' => $row[$colphone],
                    'colemail' => $row[$colemail],
                    'colcity' => $row[$colcity],
                    'coladdress' => $row[$coladdress]
                );
            }
            return;
        }

        $cnt = 0;
        $newitems = array();
        foreach ($data as $row) {

            $c = null;
            $name = $row[$colcname];
            $phone = $row[$colphone];

            if (strlen(trim($name)) == 0) {
                continue;
            }

            if (strlen(trim($phone)) > 0) {
                $c = Customer::getFirst('phone=' . Customer::qstr($phone));
            }

            if ($c == null) {

                $c = new Customer();
                $c->type = $t;
                $c->customer_name = $name;

                if (strlen($row[$colphone]) > 0) {
                    $c->phone = $row[$colphone];
                }
                if (strlen($row[$colemail]) > 0) {
                    $c->email = $row[$colemail];
                }
                if (strlen($row[$colcity]) > 0) {
                    $c->city = $row[$colcity];
                }
                if (strlen($row[$coladdress]) > 0) {
                    $c->address = $row[$coladdress];
                }


                $c->save();
                $cnt++;
            }
        }

        $this->setSuccess("imported_customers ", $cnt);
    }

    public function onNImport($sender)
    {
        $store = $this->nform->nstore->getValue();
        $c = $this->nform->ncust->getKey();
        $checkname = $this->nform->ncheckname->isChecked();

        $preview = $this->nform->npreview->isChecked();
        $passfirst = $this->nform->npassfirst->isChecked();
        $this->_tvars['preview3'] = false;

        $colname = $this->nform->ncolname->getValue();
        $colcode = $this->nform->ncolcode->getValue();
        $colbarcode = $this->nform->ncolbarcode->getValue();
        $colqty = $this->nform->ncolqty->getValue();
        $colprice = $this->nform->ncolprice->getValue();
        $colmsr = $this->nform->ncolmsr->getValue();
        $snumber = $this->nform->nsnumber->getValue();
        $sdate = $this->nform->nsdate->getValue();

        if ($colname === '0') {
            $this->setError('noselcolname');
            return;
        }
        if ($colqty === '0') {
            $this->setError('noselcolqty');
            return;
        }

        if ($c == 0) {
            $this->setError('noselsender');
            return;
        }

        $file = $this->nform->nfilename->getFile();
        if (strlen($file['tmp_name']) == 0) {

            $this->setError('noselfile');
            return;
        }

        $data = array();
        $oSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']); // Вариант и для xls и xlsX


        $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();

        for ($iRow = ($passfirst ? 2 : 1); $iRow <= $oCells->getHighestRow(); $iRow++) {

            $row = array();
            for ($iCol = 'A'; $iCol <= $oCells->getHighestColumn(); $iCol++) {
                $oCell = $oCells->get($iCol . $iRow);
                if ($oCell) {
                    $row[$iCol] = $oCell->getValue();
                }
            }
            $data[$iRow] = $row;
        }

        unset($oSpreadsheet);

        if ($preview) {

            $this->_tvars['preview3'] = true;
            $this->_tvars['list'] = array();
            foreach ($data as $row) {

                $this->_tvars['list'][] = array(
                    'colname' => $row[$colname],
                    'colcode' => $row[$colcode],
                    'colbarcode' => $row[$colbarcode],
                    'colqty' => $row[$colqty],
                    'colmsr' => $row[$colmsr],
                    'colprice' => $row[$colprice]
                );
            }
            return;
        }

        $cnt = 0;
        $items = array();
        foreach ($data as $row) {


            $item = null;
            $itemname = trim($row[$colname]);
            $itemcode = trim($row[$colcode]);
            if (strlen($itemname) > 0) {

                if (strlen($itemcode) > 0) {
                    $item = Item::getFirst('item_code=' . Item::qstr($itemcode));
                }
                if ($checkname == true && $item == null) {
                    $item = Item::getFirst('itemname=' . Item::qstr($itemname));
                }

                $price = str_replace(',', '.', trim($row[$colprice]));
                $qty = str_replace(',', '.', trim($row[$colqty]));

                if ($item == null) {
                    $item = new Item();
                    $item->itemname = $itemname;
                    if (strlen($row[$colcode]) > 0) {
                        $item->item_code = trim($row[$colcode]);
                    }
                    if (strlen($row[$colmsr - 1]) > 0) {
                        $item->msr = trim($row[$colmsr]);
                    }


                    $item->save();
                }

                if ($row[$snumber]) $item->snumber = trim($row[$snumber]);
                if ($row[$sdate]) $item->sdate = strtotime(date("d-m-Y", ($row[$sdate] - 25569) * 86400));


                if ($qty > 0) {
                    $item->price = $price;
                    $item->quantity = $qty;

                    $items[] = $item;
                }
            }
        }
        if (count($items) > 0) {
            $doc = \App\Entity\Doc\Document::create('GoodsReceipt');
            $doc->document_number = $doc->nextNumber();
            if (strlen($doc->document_number) == 0) {
                $doc->document_number = "ПН00001";
            }
            $doc->document_date = time();

            $amount = 0;
            $itlist = array();
            foreach ($items as $item) {
                $itlist[] = $item;
                $amount = $amount + ($item->quantity * $item->price);
            }
            $doc->packDetails('detaildata', $itlist);
            $doc->amount = H::fa($amount);
            $doc->payamount = 0;
            $doc->payed = 0;
            $doc->notes = 'Импорт с Excel';
            $doc->headerdata['store'] = $store;
            $doc->customer_id = $c;
            $doc->headerdata['customer_name'] = $this->nform->ncust->getText();

            $doc->save();
            $doc->updateStatus(\App\Entity\Doc\Document::STATE_NEW);
            App::Redirect("\\App\\Pages\\Doc\\GoodsReceipt", $doc->document_id);
        }
    }

}
