<?php

namespace App\Pages\Reference;

use App\Entity\Category;
use App\Entity\Item;
use App\Entity\ItemSet;
use App\Helper as H;
use App\System;
use Zippy\Html\DataList\ArrayDataSource;
use Zippy\Html\DataList\DataView;
use Zippy\Html\Form\AutocompleteTextInput;
use Zippy\Html\Form\Button;
use Zippy\Html\Form\CheckBox;
use Zippy\Html\Form\DropDownChoice;
use Zippy\Html\Form\Form;
use Zippy\Html\Form\SubmitButton;
use Zippy\Html\Form\TextArea;
use Zippy\Html\Form\TextInput;
use Zippy\Html\Label;
use Zippy\Html\Link\ClickLink;
use Zippy\Html\Panel;
use Zippy\Html\Link\SubmitLink;

class ItemList extends \App\Pages\Base
{

    private $_item;
    private $_copy     = false;
    private $_pitem_id = 0;
    public  $_itemset  = array();

    public function __construct($add = false) {
        parent::__construct();
        if (false == \App\ACL::checkShowRef('ItemList')) {
            return;
        }

        $this->add(new Form('filter'))->onSubmit($this, 'OnFilter');
        $this->filter->add(new CheckBox('showdis'));
        $this->filter->add(new TextInput('searchbrand'));
        $this->filter->searchbrand->setDataList(Item::getManufacturers());


        $this->filter->add(new TextInput('searchkey'));
        $catlist = array();
        $catlist[-1] = H::l("withoutcat");
        foreach (Category::getList() as $k => $v) {
            $catlist[$k] = $v;
        }
        $this->filter->add(new DropDownChoice('searchcat', $catlist, 0));

        $this->add(new Panel('itemtable'))->setVisible(true);
        $this->itemtable->add(new ClickLink('addnew'))->onClick($this, 'addOnClick');

        $this->itemtable->add(new Form('listform'));

        $this->itemtable->listform->add(new DataView('itemlist', new ItemDataSource($this), $this, 'itemlistOnRow'));
        $this->itemtable->listform->itemlist->setPageSize(H::getPG());
        $this->itemtable->listform->add(new \Zippy\Html\DataList\Paginator('pag', $this->itemtable->listform->itemlist));
        $this->itemtable->listform->itemlist->setSelectedClass('table-success');
        $this->itemtable->listform->add(new SubmitLink('deleteall'))->onClick($this, 'OnDelAll');
        $this->itemtable->listform->add(new SubmitLink('printall'))->onClick($this, 'OnPrintAll', true);

        $catlist = Category::findArray("cat_name", "cat_id not in (select COALESCE(parent_id,0) from item_cat )", "cat_name");


        $this->itemtable->listform->add(new DropDownChoice('allcat', $catlist, 0))->onChange($this, 'onAllCat');

        $this->add(new Form('itemdetail'))->setVisible(false);
        $this->itemdetail->add(new TextInput('editname'));
        $this->itemdetail->add(new TextInput('editshortname'));
        $this->itemdetail->add(new TextInput('editprice1'));
        $this->itemdetail->add(new TextInput('editprice2'));
        $this->itemdetail->add(new TextInput('editprice3'));
        $this->itemdetail->add(new TextInput('editprice4'));
        $this->itemdetail->add(new TextInput('editprice5'));
        $this->itemdetail->add(new TextInput('editmanufacturer'));
        $this->itemdetail->add(new TextInput('editurl'));
        $common = System::getOptions('common');
        if (strlen($common['price1']) > 0) {
            $this->itemdetail->editprice1->setVisible(true);
            $this->itemdetail->editprice1->setAttribute('placeholder', $common['price1']);
        } else {
            $this->itemdetail->editprice1->setVisible(false);
        }
        if (strlen($common['price2']) > 0) {
            $this->itemdetail->editprice2->setVisible(true);
            $this->itemdetail->editprice2->setAttribute('placeholder', $common['price2']);
        } else {
            $this->itemdetail->editprice2->setVisible(false);
        }
        if (strlen($common['price3']) > 0) {
            $this->itemdetail->editprice3->setVisible(true);
            $this->itemdetail->editprice3->setAttribute('placeholder', $common['price3']);
        } else {
            $this->itemdetail->editprice3->setVisible(false);
        }
        if (strlen($common['price4']) > 0) {
            $this->itemdetail->editprice4->setVisible(true);
            $this->itemdetail->editprice4->setAttribute('placeholder', $common['price4']);
        } else {
            $this->itemdetail->editprice4->setVisible(false);
        }
        if (strlen($common['price5']) > 0) {
            $this->itemdetail->editprice5->setVisible(true);
            $this->itemdetail->editprice5->setAttribute('placeholder', $common['price5']);
        } else {
            $this->itemdetail->editprice5->setVisible(false);
        }
        $this->itemdetail->add(new TextInput('editbarcode'));
        $this->itemdetail->add(new TextInput('editminqty'));
        $this->itemdetail->add(new TextInput('editzarp'));
        $this->itemdetail->add(new TextInput('editweight'));
        $this->itemdetail->add(new TextInput('editmaxsize'));
        $this->itemdetail->add(new TextInput('editvolume'));
        $this->itemdetail->add(new TextInput('editcustomsize'));
        $this->itemdetail->add(new TextInput('editwarranty'));
        $this->itemdetail->add(new TextInput('editlost'));

        $this->itemdetail->add(new TextInput('editcell'));
        $this->itemdetail->add(new TextInput('editmsr'));

        $this->itemdetail->add(new DropDownChoice('editcat', Category::findArray("cat_name", "cat_id not in (select coalesce(parent_id,0) from item_cat  )", "cat_name"), 0));
        $this->itemdetail->add(new TextInput('editcode'));
        $this->itemdetail->add(new TextArea('editdescription'));
        $this->itemdetail->add(new CheckBox('editdisabled'));
        $this->itemdetail->add(new CheckBox('edituseserial'));
        $this->itemdetail->add(new CheckBox('editnoprice'));
        $this->itemdetail->add(new CheckBox('editnoshop'));
        $this->itemdetail->add(new CheckBox('editautooutcome'));
        $this->itemdetail->add(new CheckBox('editautoincome'));
        $this->itemdetail->add(new \Zippy\Html\Image('editimage', '/loadimage.php?id=0'));
        $this->itemdetail->add(new \Zippy\Html\Form\File('editaddfile'));
        $this->itemdetail->add(new CheckBox('editdelimage'));
        $this->itemdetail->add(new DropDownChoice('edittype', Item::getTypes()));

        $this->itemdetail->add(new SubmitButton('save'))->onClick($this, 'OnSubmit');
        $this->itemdetail->add(new Button('cancel'))->onClick($this, 'cancelOnClick');

        $this->add(new Panel('setpanel'))->setVisible(false);
        $this->setpanel->add(new DataView('setlist', new ArrayDataSource($this, '_itemset'), $this, 'itemsetlistOnRow'));
        $this->setpanel->add(new Form('setform'))->onSubmit($this, 'OnAddSet');
        $this->setpanel->setform->add(new AutocompleteTextInput('editsname'))->onText($this, 'OnAutoSet');
        $this->setpanel->setform->add(new TextInput('editsqty', 1));

        $this->setpanel->add(new Label('stitle'));
        $this->setpanel->add(new ClickLink('backtolist', $this, "onback"));


        $this->_tvars['hp1'] = strlen($common['price1']) > 0 ? $common['price1'] : false;
        $this->_tvars['hp2'] = strlen($common['price2']) > 0 ? $common['price2'] : false;
        $this->_tvars['hp3'] = strlen($common['price3']) > 0 ? $common['price3'] : false;
        $this->_tvars['hp4'] = strlen($common['price4']) > 0 ? $common['price4'] : false;
        $this->_tvars['hp5'] = strlen($common['price5']) > 0 ? $common['price5'] : false;


        if ($add == false) {
            $this->itemtable->listform->itemlist->Reload();
        } else {
            $this->addOnClick(null);
        }
    }

    public function itemlistOnRow(\Zippy\Html\DataList\DataRow $row) {
        $item = $row->getDataItem();
        $row->setAttribute('style', $item->disabled == 1 ? 'color: #aaa' : null);

        $row->add(new Label('itemname', $item->itemname));
        $row->add(new Label('code', $item->item_code));
        $row->add(new Label('msr', $item->msr));
        $row->add(new Label('cat_name', $item->cat_name));
        $row->add(new Label('manufacturer', $item->manufacturer));

        $row->add(new Label('price1', $item->price1));
        $row->add(new Label('price2', $item->price2));
        $row->add(new Label('price3', $item->price3));
        $row->add(new Label('price4', $item->price4));
        $row->add(new Label('price5', $item->price5));

        $row->add(new Label('hasnotes'))->setVisible(strlen($item->description) > 0);
        $row->hasnotes->setAttribute('title', htmlspecialchars_decode($item->description));
        $row->setAttribute('style', $item->disabled == 1 ? 'color: #aaa' : null);

        $row->add(new Label('cell', $item->cell));
        $row->add(new Label('inseria'))->setVisible($item->useserial);
        $row->add(new Label('hasaction'))->setVisible($item->hasAction());

        $row->add(new ClickLink('copy'))->onClick($this, 'copyOnClick');
        $row->add(new ClickLink('edit'))->onClick($this, 'editOnClick');

        $row->add(new ClickLink('set'))->onClick($this, 'setOnClick');
        $row->set->setVisible($item->item_type == Item::TYPE_PROD || $item->item_type == Item::TYPE_HALFPROD);

        $row->add(new ClickLink('printqr'))->onClick($this, 'printQrOnClick', true);
        $row->printqr->setVisible(strlen($item->url) > 0);

        $row->add(new \Zippy\Html\Link\BookmarkableLink('imagelistitem'))->setValue("/loadimage.php?id={$item->image_id}");
        $row->imagelistitem->setAttribute('href', "/loadimage.php?id={$item->image_id}");
        $row->imagelistitem->setAttribute('data-gallery', $item->image_id);
        if ($item->image_id == 0) {
            $row->imagelistitem->setVisible(false);
        }

        $row->add(new CheckBox('seldel', new \Zippy\Binding\PropertyBinding($item, 'seldel')));

    }


    public function copyOnClick($sender) {
        $this->editOnClick($sender);
        $this->_copy = true;
        $this->_item->item_id = 0;
        $this->itemdetail->editcode->setText('');
        $this->itemdetail->editbarcode->setText('');
        if (System::getOption("common", "autoarticle") == 1) {
            $this->itemdetail->editcode->setText(Item::getNextArticle());
        }
    }

    public function editOnClick($sender) {
        $this->_copy = false;
        $item = $sender->owner->getDataItem();
        $this->_item = Item::load($item->item_id);

        $this->itemtable->setVisible(false);
        $this->itemdetail->setVisible(true);

        $this->itemdetail->editname->setText($this->_item->itemname);
        $this->itemdetail->editshortname->setText($this->_item->shortname);
        $this->itemdetail->editprice1->setText($this->_item->price1);
        $this->itemdetail->editprice2->setText($this->_item->price2);
        $this->itemdetail->editprice3->setText($this->_item->price3);
        $this->itemdetail->editprice4->setText($this->_item->price4);
        $this->itemdetail->editprice5->setText($this->_item->price5);
        $this->itemdetail->editcat->setValue($this->_item->cat_id);

        $this->itemdetail->editmanufacturer->setText($this->_item->manufacturer);
        $this->itemdetail->editdescription->setText($this->_item->description);
        $this->itemdetail->editcode->setText($this->_item->item_code);
        $this->itemdetail->editbarcode->setText($this->_item->bar_code);
        $this->itemdetail->editmsr->setText($this->_item->msr);
        $this->itemdetail->editmaxsize->setText($this->_item->maxsize);
        $this->itemdetail->editvolume->setText($this->_item->volume);
        $this->itemdetail->editlost->setText($this->_item->lost);
        $this->itemdetail->editcustomsize->setText($this->_item->customsize);
        $this->itemdetail->editwarranty->setText($this->_item->warranty);
        $this->itemdetail->edittype->setValue($this->_item->item_type);

        $this->itemdetail->editurl->setText($this->_item->url);
        $this->itemdetail->editcell->setText($this->_item->cell);
        $this->itemdetail->editminqty->setText(\App\Helper::fqty($this->_item->minqty));
        $this->itemdetail->editzarp->setText(\App\Helper::fqty($this->_item->zarp));
        $this->itemdetail->editdisabled->setChecked($this->_item->disabled);
        $this->itemdetail->edituseserial->setChecked($this->_item->useserial);
        $this->itemdetail->editnoshop->setChecked($this->_item->noshop);
        $this->itemdetail->editnoprice->setChecked($this->_item->noprice);
        $this->itemdetail->editautooutcome->setChecked($this->_item->autooutcome);
        $this->itemdetail->editautoincome->setChecked($this->_item->autoincome);
        if ($this->_item->image_id > 0) {
            $this->itemdetail->editdelimage->setChecked(false);
            $this->itemdetail->editdelimage->setVisible(true);
            $this->itemdetail->editimage->setVisible(true);
            $this->itemdetail->editimage->setUrl('/loadimage.php?id=' . $this->_item->image_id);
        } else {
            $this->itemdetail->editdelimage->setVisible(false);
            $this->itemdetail->editimage->setVisible(false);
        }

        $this->itemtable->listform->itemlist->setSelectedRow($sender->getOwner());
        $this->itemtable->listform->itemlist->Reload(false);

        $this->filter->searchbrand->setDataList(Item::getManufacturers());

    }

    public function addOnClick($sender) {
        $this->_copy = false;
        $this->itemtable->setVisible(false);
        $this->itemdetail->setVisible(true);
        // Очищаем  форму
        $this->itemdetail->clean();
        $this->itemdetail->editmsr->setText('шт');
        $this->itemdetail->editimage->setVisible(false);
        $this->itemdetail->editdelimage->setVisible(false);
        $this->itemdetail->editnoprice->setChecked(false);
        $this->itemdetail->editnoshop->setChecked(false);
        $this->itemdetail->editautooutcome->setChecked(false);
        $this->itemdetail->editautoincome->setChecked(false);
        $this->_item = new Item();

        if (System::getOption("common", "autoarticle") == 1) {
            $this->itemdetail->editcode->setText(Item::getNextArticle());
        }
        $this->filter->searchbrand->setDataList(Item::getManufacturers());

    }

    public function cancelOnClick($sender) {
        $this->itemtable->setVisible(true);
        $this->itemdetail->setVisible(false);
    }

    public function OnFilter($sender) {
        $this->itemtable->listform->itemlist->Reload();
    }

    public function OnSubmit($sender) {
        if (false == \App\ACL::checkEditRef('ItemList')) {
            return;
        }

        $this->_item->itemname = $this->itemdetail->editname->getText();
        if (strlen($this->_item->itemname) == 0) {
            $this->setError('entername');
            return;
        }
        $this->_item->shortname = $this->itemdetail->editshortname->getText();
        $this->_item->cat_id = $this->itemdetail->editcat->getValue();
        $this->_item->price1 = $this->itemdetail->editprice1->getText();
        $this->_item->price2 = $this->itemdetail->editprice2->getText();
        $this->_item->price3 = $this->itemdetail->editprice3->getText();
        $this->_item->price4 = $this->itemdetail->editprice4->getText();
        $this->_item->price5 = $this->itemdetail->editprice5->getText();

        $this->_item->item_code = trim($this->itemdetail->editcode->getText());
        $this->_item->manufacturer = trim($this->itemdetail->editmanufacturer->getText());

        $this->_item->bar_code = trim($this->itemdetail->editbarcode->getText());
        $this->_item->url = trim($this->itemdetail->editurl->getText());
        $this->_item->msr = $this->itemdetail->editmsr->getText();
        $this->_item->weight = $this->itemdetail->editweight->getText();
        $this->_item->maxsize = $this->itemdetail->editmaxsize->getText();
        $this->_item->volume = $this->itemdetail->editvolume->getText();
        $this->_item->lost = $this->itemdetail->editlost->getText();
        $this->_item->customsize = $this->itemdetail->editcustomsize->getText();
        $this->_item->warranty = $this->itemdetail->editwarranty->getText();
        $this->_item->item_type = $this->itemdetail->edittype->getValue();

        $this->_item->cell = $this->itemdetail->editcell->getText();
        $this->_item->minqty = $this->itemdetail->editminqty->getText();
        $this->_item->zarp = $this->itemdetail->editzarp->getText();
        $this->_item->description = $this->itemdetail->editdescription->getText();
        $this->_item->disabled = $this->itemdetail->editdisabled->isChecked() ? 1 : 0;
        $this->_item->useserial = $this->itemdetail->edituseserial->isChecked() ? 1 : 0;

        $this->_item->noprice = $this->itemdetail->editnoprice->isChecked() ? 1 : 0;
        $this->_item->noshop = $this->itemdetail->editnoshop->isChecked() ? 1 : 0;
        $this->_item->autooutcome = $this->itemdetail->editautooutcome->isChecked() ? 1 : 0;
        $this->_item->autoincome = $this->itemdetail->editautoincome->isChecked() ? 1 : 0;

        //проверка  уникальности артикула
        if (strlen($this->_item->item_code) > 0 && System::getOption("common", "nocheckarticle") != 1 ) {
            $code = Item::qstr($this->_item->item_code);
            $cnt = Item::findCnt("item_id <> {$this->_item->item_id} and item_code={$code} ");
            if ($cnt > 0 ) {
                //пытаемся генерить еще раз 
                if ($this->_item->item_id == 0 && System::getOption("common", "autoarticle") == 1) {
                    $this->_item->item_code = Item::getNextArticle();
                    $this->itemdetail->editcode->setText($this->_item->item_code);

                    $cnt = Item::findCnt("item_id <> {$this->_item->item_id} and item_code={$code} ");
                    if ($cnt > 0) {

                        $this->setError('itemcode_exists');
                        return;
                    }
                } else {
                    $this->setError('itemcode_exists');
                    return;
                }
            }
        }

        //проверка  уникальности штрих кода
        if (strlen($this->_item->bar_code) > 0) {
            $code = Item::qstr($this->_item->bar_code);
            $cnt = Item::findCnt("item_id <> {$this->_item->item_id} and bar_code={$code} ");
            if ($cnt > 0) {
                $this->setWarn('barcode_exists');
            }
        }
        $printer = System::getOptions('printer');

        if (intval($printer['pmaxname']) > 0 && strlen($this->_item->shortname) > intval($printer['pmaxname'])) {

            $this->setWarn('tolongshortname', $printer['pmaxname']);

        }


        $itemname = Item::qstr($this->_item->itemname);
        $code = Item::qstr($this->_item->item_code);
        $cnt = Item::findCnt("item_id <> {$this->_item->item_id} and itemname={$itemname} and item_code={$code} ");
        if ($cnt > 0) {
            $this->setError('itemnamecode_exists');
            return;
        }

        //delete image
        if ($this->itemdetail->editdelimage->isChecked()) {
            if ($this->_item->image_id > 0) {
                \App\Entity\Image::delete($this->_item->image_id);
            }
            $this->_item->image_id = 0;
        }

        if ($this->_item->image_id > 0 && $this->_copy == true) {
            $image = \App\Entity\Image::load($this->_item->image_id);
            $image->image_id = 0;
            $image->save();
            $this->_item->image_id = $image->image_id;

        }

        $this->_item->save();

        $file = $this->itemdetail->editaddfile->getFile();
        if (strlen($file["tmp_name"]) > 0) {
            $imagedata = getimagesize($file["tmp_name"]);

            if (preg_match('/(gif|png|jpeg)$/', $imagedata['mime']) == 0) {
                $this->setError('invalidformatimage');
                return;
            }

            if ($imagedata[0] * $imagedata[1] > 10000000) {

                $this->setError('toobigimage');
                return;
            }

            $image = new \App\Entity\Image();
            $image->content = file_get_contents($file['tmp_name']);
            $image->mime = $imagedata['mime'];

            if ($imagedata[0] != $imagedata[1]) {
                $thumb = new \App\Thumb($file['tmp_name']);
                if ($imagedata[0] > $imagedata[1]) {
                    $thumb->cropFromCenter($imagedata[1], $imagedata[1]);
                }
                if ($imagedata[0] < $imagedata[1]) {
                    $thumb->cropFromCenter($imagedata[0], $imagedata[0]);
                }


                $image->content = $thumb->getImageAsString();
                $thumb->resize(256, 256);
                $image->thumb = $thumb->getImageAsString();
            }

            $this->filter->searchbrand->setDataList(Item::getManufacturers());


            $image->save();
            $this->_item->image_id = $image->image_id;
            $this->_item->Save();
        }

        $this->itemtable->listform->itemlist->Reload(false);

        $this->itemtable->setVisible(true);
        $this->itemdetail->setVisible(false);
    }

    //комплекты
    public function onback($sender) {
        $this->setpanel->setVisible(false);
        $this->itemtable->setVisible(true);
    }

    public function setOnClick($sender) {
        $item = $sender->owner->getDataItem();
        $item = Item::load($item->item_id);

        $this->_pitem_id = $item->item_id;
        $this->_itemset = ItemSet::find("pitem_id=" . $item->item_id, "itemname");
        $this->setpanel->setVisible(true);
        $this->itemtable->setVisible(false);

        $this->setpanel->stitle->setText($item->itemname);

        $this->setpanel->setlist->Reload();
    }

    public function itemsetlistOnRow(\Zippy\Html\DataList\DataRow $row) {
        $item = $row->getDataItem();
        $row->add(new Label('sname', $item->itemname));
        $row->add(new Label('scode', $item->item_code));
        $row->add(new Label('sqty', H::fqty($item->qty)));
        $row->add(new ClickLink('sdel'))->onClick($this, 'ondelset');
    }

    public function OnAutoSet($sender) {
        $text = Item::qstr('%' . $sender->getText() . '%');
        $in = "(" . $this->_pitem_id;
        foreach ($this->_itemset as $is) {
            $in .= "," . $is->item_id;
        }

        $in .= ")";
        return Item::findArray('itemname', "coalesce(item_type,0)  in (2,5) and  item_id not in {$in} and (itemname like {$text} or item_code like {$text}) and disabled <> 1", 'itemname');
    }

    public function OnAddSet($sender) {
        $id = $sender->editsname->getKey();
        if ($id == 0) {
            $this->setError("noselitem");
            return;
        }

        $qty = $sender->editsqty->getText();

        $set = new ItemSet();
        $set->pitem_id = $this->_pitem_id;
        $set->item_id = $id;
        $set->qty = $qty;

        $set->save();

        $this->_itemset = ItemSet::find("pitem_id=" . $this->_pitem_id, "itemname");

        $this->setpanel->setlist->Reload();
        $sender->clean();
    }

    public function ondelset($sender) {
        $item = $sender->owner->getDataItem();

        ItemSet::delete($item->set_id);

        $this->_itemset = ItemSet::find("pitem_id=" . $this->_pitem_id, "itemname");

        $this->setpanel->setlist->Reload();
    }

    public function printQrOnClick($sender) {
        $item = $sender->getOwner()->getDataItem();

        $qrCode = new \Endroid\QrCode\QrCode($item->url);
        $qrCode->setSize(100);
        $qrCode->setMargin(5);
        $qrCode->setWriterByName('png');

        $dataUri = $qrCode->writeDataUri();
        $html = "<img src=\"{$dataUri}\"  />";
        $this->updateAjax(array(), "  $('#tag').html('{$html}') ; $('#pform').modal()");

    }

    public function printOnClick($sender) {
        $item = $sender->getOwner()->getDataItem();
        $printer = \App\System::getOptions('printer');
        $pwidth = 'style="width:40mm;"';
        $pfs = 'style="font-size:16px;"';
        $pfsp = 'style="font-size:24px;"';

        if (strlen($printer['pwidth']) > 0) {
            $pwidth = 'style="width:' . $printer['pwidth'] . ' ";';
        }
        if (strlen($printer['pfontsize']) > 0) {
            $pfs = 'style="font-size:' . $printer['pfontsize'] . 'px";';
            $pfsp = 'style="font-size:' . intval(($printer['pfontsize'] * 1.5)) . 'px";';
        }


        $report = new \App\Report('item_tag.tpl');
        $header = array('width' => $pwidth, 'fsize' => $pfs, 'fsizep' => $pfsp);
        if ($printer['pname'] == 1) {

            if (strlen($item->shortname) > 0) {
                $header['name'] = $item->shortname;
            } else {
                $header['name'] = $item->itemname;
            }
        }
        $header['action'] = $item->actionprice > 0;
        $header['actionprice'] = $item->actionprice;
        $header['isap'] = false;
        if ($printer['pprice'] == 1) {
            $header['price'] = number_format($item->getPrice($printer['pricetype']), 2, '.', '');
            $header['isap'] = true;
        }
        if ($printer['pcode'] == 1) {
            $header['article'] = $item->item_code;
            $header['isap'] = true;
        }

        if ($printer['pqrcode'] == 1 && strlen($item->url) > 0) {
            $qrCode = new \Endroid\QrCode\QrCode($item->url);
            $qrCode->setSize(100);
            $qrCode->setMargin(5);
            $qrCode->setWriterByName('png');

            $dataUri = $qrCode->writeDataUri();
            $header['qrcode'] = "<img src=\"{$dataUri}\"  />";

        }
        if ($printer['pbarcode'] == 1) {
            $barcode = $item->bar_code;
            if (strlen($barcode) == 0) {
                $barcode = $item->item_code;
            }

            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $img = '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($barcode, $printer['barcodetype'])) . '">';
            $header['img'] = $img;
            $header['barcode'] = \App\Util::addSpaces($barcode);
        }


        $html = $report->generate($header);


        $this->updateAjax(array(), "  $('#tag').html('{$html}') ; $('#pform').modal()");
    }


    public function OnPrintAll($sender) {

        $items = array();
        foreach ($this->itemtable->listform->itemlist->getDataRows() as $row) {
            $item = $row->getDataItem();
            if ($item->seldel == true) {
                $items[] = $item;
            }
        }
        if (count($items) == 0) {
            return;
        }

        $printer = \App\System::getOptions('printer');
        $pwidth = "width:70mm;";
        $pheight = "height:40mm;";
        $pfs = 'style="font-size:16px;"';
        $pfs = 'style="font-size:24px;"';

        if (strlen($printer['pwidth']) > 0) {
            $pwidth = "width:" . $printer['pwidth'] . ";";
        }
        if (strlen($printer['pheight']) > 0) {
            $pheight = "height:" . $printer['pheight'] . ";";
        }
        $style = "style=\"";
        $style .= $pwidth;
        $style .= $pheight;
        $style .= "\"";

        if (strlen($printer['pfontsize']) > 0) {
            $pfs = 'style="font-size:' . $printer['pfontsize'] . 'px";';
            $pfsp = 'style="font-size:' . intval(($printer['pfontsize'] * 1.5)) . 'px";';
        }

        $htmls = "";

        foreach ($items as $item) {
            $report = new \App\Report('item_tag.tpl');
            $header = array('style' => $style, 'fsize' => $pfs, 'fsizep' => $pfsp);
            if ($printer['pname'] == 1) {

                if (strlen($item->shortname) > 0) {
                    $header['name'] = $item->shortname;
                } else {
                    $header['name'] = $item->itemname;
                }
            }
             if ($printer['pcode'] == 1) {
                $header['article'] = $item->item_code;
                $header['isap'] = true;
            }
            if ($printer['pqrcode'] == 1 && strlen($item->url) > 0) {
                $qrCode = new \Endroid\QrCode\QrCode($item->url);
                $qrCode->setSize(500);
                $qrCode->setMargin(5);
                $qrCode->setWriterByName('png');

                $dataUri = $qrCode->writeDataUri();
                $header['qrcode'] = "<img style=\"width:100px\" src=\"{$dataUri}\"  />";

            }
            if ($printer['pbarcode'] == 1) {
                $barcode = $item->bar_code;
                if (strlen($barcode) == 0) {
                    $barcode = $item->item_code;
                }
                if (strlen($barcode) == 0) {
                    continue;
                }

                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                $img = '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($barcode, $printer['barcodetype'])) . '">';
                $header['img'] = $img;
                $header['barcode'] = \App\Util::addSpaces($barcode);
            }
         
            $header['isap'] = false;
            if ($printer['pprice'] == 1) {
                $header['price'] = H::fa($item->getPurePrice($printer['pricetype']));
                $header['isap'] = true;
            }
         
            $header['action'] = $item->hasAction();  ;
            if($header['action']) {
               $header['actionprice'] = $item->getActionPrice($header['price']); 
            } 


            $htmls = $htmls . $report->generate($header);

        }
        $htmls = str_replace("\'", "", $htmls);
        $this->updateAjax(array(), "  $('#tag').html('{$htmls}') ;$('.seldel').prop('checked',null); $('#pform').modal()");


    }

    public function onAllCat($sender) {
        $cat_id = $sender->getValue();
        if ($cat_id == 0) {
            return;
        }

        $items = array();
        foreach ($this->itemtable->listform->itemlist->getDataRows() as $row) {
            $item = $row->getDataItem();
            if ($item->seldel == true) {
                $items[] = $item;
            }
        }
        if (count($items) == 0) {
            return;
        }
        $conn = \ZDB\DB::getConnect();


        foreach ($items as $item) {

            $conn->Execute("update items set  cat_id={$cat_id} where  item_id={$item->item_id}");
        }

        $this->itemtable->listform->itemlist->Reload();
        $sender->setValue(0);
    }

    public function OnDelAll($sender) {
        if (false == \App\ACL::checkDelRef('ItemList')) {
            return;
        }

        $ids = array();
        foreach ($this->itemtable->listform->itemlist->getDataRows() as $row) {
            $item = $row->getDataItem();
            if ($item->seldel == true) {
                $ids[] = $item->item_id;
            }
        }
        if (count($ids) == 0) {
            return;
        }

        $conn = \ZDB\DB::getConnect();
        $d = 0;
        $u = 0;
        foreach ($ids as $id) {
            $sql = "  select count(*)  from  store_stock where   item_id = {$id}  ";
            $cnt = $conn->GetOne($sql);
            if ($cnt > 0) {
                $u++;
                $conn->Execute("update items  set  disabled=1 where   item_id={$id}");
            } else {
                $d++;
                $conn->Execute("delete from items  where   item_id={$id}");

            }
        }


        $this->setSuccess("delitems", $d, $u);

        $this->itemtable->listform->itemlist->Reload();

    }


}

class ItemDataSource implements \Zippy\Interfaces\DataSource
{

    private $page;

    public function __construct($page) {
        $this->page = $page;
    }

    private function getWhere($p = false) {

        $form = $this->page->filter;
        $where = "1=1";
        $text = trim($form->searchkey->getText());
        $brand = trim($form->searchbrand->getText());
        $cat = $form->searchcat->getValue();
        $showdis = $form->showdis->isChecked();

        if ($cat != 0) {
            if ($cat == -1) {
                $where = $where . " and cat_id=0";
            } else {
                $where = $where . " and cat_id=" . $cat;
            }
        }

        if (strlen($brand) > 0) {

            $brand = Item::qstr($brand);
            $where = $where . " and  manufacturer like {$brand}      ";
        }


        if ($showdis == true) {

        } else {
            $where = $where . " and disabled <> 1";
        }
        if (strlen($text) > 0) {
            if ($p == false) {
                $text = Item::qstr('%' . $text . '%');
                $where = $where . " and (itemname like {$text} or item_code like {$text}  or bar_code like {$text} )  ";
            } else {
                $text = Item::qstr($text);
                $where = $where . " and (itemname = {$text} or item_code = {$text}  or bar_code like {$text} )  ";
            }
        }
        return $where;
    }

    public function getItemCount() {
        return Item::findCnt($this->getWhere());
    }

    public function getItems($start, $count, $sortfield = null, $asc = null) {
        $l = Item::find($this->getWhere(true), "itemname asc", $count, $start);
        $f = Item::find($this->getWhere(), "itemname asc", $count, $start);
        foreach ($f as $k => $v) {
            $l[$k] = $v;
        }
        return $l;
    }

    public function getItem($id) {
        return Item::load($id);
    }

}
