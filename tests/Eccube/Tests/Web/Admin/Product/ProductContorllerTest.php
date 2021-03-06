<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Tests\Web\Admin\Product;

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;

class ProductControllerTest extends AbstractAdminWebTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testRoutingAdminProductProduct()
    {
        $this->client->request('GET',
            $this->app->url('admin_product')
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testRoutingAdminProductProductNew()
    {
        $this->client->request('GET',
            $this->app->url('admin_product_product_new')
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testRoutingAdminProductProductEdit()
    {
        // before
        $TestCreator = $this->app['orm.em']
            ->getRepository('\Eccube\Entity\Member')
            ->find(1);
        $TestProduct = $this->newTestProduct($TestCreator);
        $this->app['orm.em']->persist($TestProduct);
        $this->app['orm.em']->flush();
        $test_product_id = $this->app['eccube.repository.product']
            ->findOneBy(array(
                'name' => $TestProduct->getName()
            ))
            ->getId();

        // main
        $this->client->request('GET',
            $this->app->url('admin_product_product_edit', array('id' => $test_product_id))
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // after
        $this->app['orm.em']->remove($TestProduct);
        $this->app['orm.em']->flush();
    }

    private function newTestProduct($TestCreator)
    {
        $TestProduct = new \Eccube\Entity\Product();
        $Disp = $this->app['orm.em']->getRepository('Eccube\Entity\Master\Disp')->find(1);
        $TestProduct->setName('テスト商品')
            ->setStatus($Disp)
            ->setNote('test note')
            ->setDescriptionList('テスト商品 商品説明(リスト)')
            ->setDescriptionDetail('テスト商品 商品説明(詳細)')
            ->setFreeArea('フリー記載')
            ->setDelFlg(0)
            ->setCreator($TestCreator);

        return $TestProduct;
    }
}