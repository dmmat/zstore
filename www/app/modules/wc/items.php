<?php

namespace App\Modules\WC;

use App\Entity\Item;
use App\Entity\Category;
use App\Helper as H;
use App\System;
use Zippy\Binding\PropertyBinding as Prop;
use Zippy\Html\DataList\ArrayDataSource;
use Zippy\Html\DataList\DataView;
use Zippy\Html\Form\CheckBox;
use Zippy\Html\Form\DropDownChoice;
use Zippy\Html\Form\Form;
use Zippy\Html\Label;
use Zippy\Html\Link\ClickLink;
use Zippy\WebApplication as App;

class Items extends \App\Pages\Base
{

    public $_items = array();

    public function __construct() {
        parent::__construct();

        if (strpos(System::getUser()->modules, 'woocomerce') === false && System::getUser()->rolename != 'admins') {
            System::setErrorMsg(H::l('noaccesstopage'));

            App::RedirectError();
            return;
        }
        $modules = System::getOptions("modules");

        $this->add(new Form('filter'))->onSubmit($this, 'filterOnSubmit');
         $this->filter->add(new DropDownChoice('searchcat', Category::getList(), 0));

        $this->add(new Form('exportform'))->onSubmit($this, 'exportOnSubmit');

        $this->exportform->add(new DataView('newitemlist', new ArrayDataSource(new Prop($this, '_items')), $this, 'itemOnRow'));
        $this->exportform->newitemlist->setPageSize(H::getPG());
        $this->exportform->add(new \Zippy\Html\DataList\Paginator('pag', $this->exportform->newitemlist));

        $this->add(new ClickLink('updateqty'))->onClick($this, 'onUpdateQty');
        $this->add(new ClickLink('updateprice'))->onClick($this, 'onUpdatePrice');
        $this->add(new ClickLink('getitems'))->onClick($this, 'onGetItems');
    }

    public function filterOnSubmit($sender) {
        $this->_items = array();
        $modules = System::getOptions("modules");

        $client = \App\Modules\WC\Helper::getClient();
        $skus = array();

        try {
            $data = $client->get('products', array('status' => 'publish'));
        } catch(\Exception $ee) {
            $this->setError($ee->getMessage());
            return;
        }

        foreach ($data as $p) {
            if (strlen($p->sku) > 0) {
                $skus[] = $p->sku;
            }
        }
        unset($data);
        $cat_id=$sender->searchcat->getValue(0);       
        
        $w = "disabled <> 1";
        if($cat_id >0)  {
            $w .= " and cat_id=".$cat_id;
        }
        $items = Item::find($w, "itemname");
        foreach ($items as $item) {
            if (strlen($item->item_code) == 0) {
                continue;
            }
            if (in_array($item->item_code, $skus)) {
                continue;
            } //уже  в  магазине

            $item->qty = $item->getQuantity();

            if (strlen($item->qty) == 0) {
                $item->qty = 0;
            }
            $this->_items[] = $item;
        }

        $this->exportform->newitemlist->Reload();
    }

    public function itemOnRow($row) {
        $modules = System::getOptions("modules");

        $item = $row->getDataItem();
        $row->add(new CheckBox('ch', new Prop($item, 'ch')));
        $row->add(new Label('name', $item->itemname));
        $row->add(new Label('code', $item->item_code));
        $row->add(new Label('qty', \App\Helper::fqty($item->qty)));
        $row->add(new Label('price', $item->getPrice($modules['ocpricetype'])));
        $row->add(new Label('desc', $item->desription));
    }

    //экспорт товара  в  магазин
    public function exportOnSubmit($sender) {
        $modules = System::getOptions("modules");
        $client = \App\Modules\WC\Helper::getClient();

        $elist = array();
        foreach ($this->_items as $item) {
            if ($item->ch == false) {
                continue;
            }
            $elist[] = array('name'           => $item->itemname,
                //  'short_description' => $item->description,
                             'sku'            => $item->item_code,
                             'manage_stock'   => true,
                             'stock_quantity' => (string)\App\Helper::fqty($item->qty),
                             'price'          => (string)$item->getPrice($modules['wcpricetype']),
                             'regular_price'  => (string)$item->getPrice($modules['wcpricetype'])
            );
        }
        if (count($elist) == 0) {
            $this->setError('noselitem');
            return;
        }

        try {
            foreach ($elist as $p) {

                $data = $client->post('products', $p);
            }
        } catch(\Exception $ee) {
            $this->setError($ee->getMessage());
            return;
        }


        $this->setSuccess("exported_items", count($elist));

        //обновляем таблицу
        $this->filterOnSubmit(null);
    }

    //обновление  количества в  магазине
    public function onUpdateQty($sender) {
        $modules = System::getOptions("modules");
        $client = \App\Modules\WC\Helper::getClient();

        try {
            $data = $client->get('products', array('status' => 'publish'));
        } catch(\Exception $ee) {
            $this->setError($ee->getMessage());
            return;
        }

        $skulist = array();

        foreach ($data as $p) {
            if (strlen($p->sku) == 0) {
                continue;
            }
            $skulist[$p->sku] = $p->id;
        }
        unset($data);

        $elist = array();
        $items = Item::find("disabled <> 1  ");
        foreach ($items as $item) {
            if (strlen($item->item_code) == 0) {
                continue;
            }
            if ($skulist[$item->item_code] > 0) {
                $qty = $item->getQuantity();
                if ($qty > 0) {
                    $elist[$item->item_code] = $qty;
                }
            }
        }
        $data = array('update' => array());
        foreach ($elist as $sku => $qty) {

            $data['update'][] = array('id' => $skulist[$sku], 'stock_quantity' => (string)$qty);
        }

        try {
            $client->post('products/batch', $data);
        } catch(\Exception $ee) {
            $this->setError($ee->getMessage());
            return;
        }
        $this->setSuccess("refreshed_items", count($data['update']));
    }

    //обновление цен в  магазине    
    public function onUpdatePrice($sender) {
        $modules = System::getOptions("modules");
        $client = \App\Modules\WC\Helper::getClient();
        $skulist = array();
        try {
            $data = $client->get('products', array('status' => 'publish'));
        } catch(\Exception $ee) {
            $this->setError($ee->getMessage());
            return;
        }

        $sku = array();
        foreach ($data as $p) {
            if (strlen($p->sku) == 0) {
                continue;
            }
            $skulist[$p->sku] = $p->id;
        }
        unset($data);

        $elist = array();
        $items = Item::find("disabled <> 1  ");
        foreach ($items as $item) {
            if (strlen($item->item_code) == 0) {
                continue;
            }
            if ($skulist[$item->item_code] > 0) {
                $price = $item->getPrice($modules['wcpricetype']);
                if ($price > 0) {
                    $elist[$item->item_code] = $price;
                }
            }
        }
        $data = array('update' => array());
        foreach ($elist as $sku => $price) {

            $data['update'][] = array('id' => $skulist[$sku], 'price' => (string)$price, 'regular_price' => (string)$price);
        }

        try {
            $client->post('products/batch', $data);
        } catch(\Exception $ee) {
            $this->setError($ee->getMessage());
            return;
        }
        $this->setSuccess("refreshed_items", count($data['update']));
    }

    //импорт товара с  магазина
    public function onGetItems($sender) {
        $modules = System::getOptions("modules");
        $common = System::getOptions("common");

        $client = \App\Modules\WC\Helper::getClient();
        $i = 0;

        $page = 1;
        while(true) {


            try {
                $data = $client->get('products', array('status' => 'publish', 'page' => $page, 'per_page' => 100));
            } catch(\Exception $ee) {
                $this->setError($ee->getMessage());
                return;
            }
            $page++;

            $c = count($data);
            if ($c == 0) {
                break;
            }
            foreach ($data as $product) {

                if (strlen($product->sku) == 0) {
                    continue;
                }
                $cnt = Item::findCnt("item_code=" . Item::qstr($product->sku));
                if ($cnt > 0) {
                    continue;
                } //уже  есть с  таким  артикулом

                $product->name = str_replace('&quot;', '"', $product->name);
                $item = new Item();
                $item->item_code = $product->sku;
                $item->itemname = $product->name;
                //   $item->description = $product->short_description;

                if ($modules['wcpricetype'] == 'price1') {
                    $item->price1 = $product->price;
                }
                if ($modules['wcpricetype'] == 'price2') {
                    $item->price2 = $product->price;
                }
                if ($modules['wcpricetype'] == 'price3') {
                    $item->price3 = $product->price;
                }
                if ($modules['wcpricetype'] == 'price4') {
                    $item->price4 = $product->price;
                }
                if ($modules['wcpricetype'] == 'price5') {
                    $item->price5 = $product->price;
                }


                if ($common['useimages'] == 1) {
                    foreach ($product->images as $im) {

                        $im = @file_get_contents($im->src);
                        if (strlen($im) > 0) {
                            $imagedata = getimagesizefromstring($im);
                            $image = new \App\Entity\Image();
                            $image->content = $im;
                            $image->mime = $imagedata['mime'];

                            $image->save();
                            $item->image_id = $image->image_id;
                            break;
                        }
                    }
                }

                $item->save();
                $i++;
            }
        }

        $this->setSuccess("loaded_items", $i);
    }

}
