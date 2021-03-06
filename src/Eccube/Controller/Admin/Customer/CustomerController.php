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


namespace Eccube\Controller\Admin\Customer;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerController
{
    public function index(Application $app, Request $request)
    {
        $pagination = null;
        $searchForm = $app['form.factory']
            ->createBuilder('admin_search_customer')
            ->getForm();

        $searchForm->handleRequest($request);
        $searchData = array();
        if ($searchForm->isValid()) {
            $searchData = $searchForm->getData();
        }

        if ('POST' === $request->getMethod()) {
            $qb = $app['orm.em']
                ->getRepository('Eccube\Entity\Customer')
                ->getQueryBuilderBySearchData($searchData);

            $pagination = $app['paginator']()->paginate(
                $qb,
                empty($searchData['pageno']) ? 1 : $searchData['pageno'],
                empty($searchData['pagemax']) ? 10 : $searchData['pagemax']->getId()
            );
        }

        return $app->render('Customer/index.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
        ));
    }

    public function resend(Application $app, Request $request, $id)
    {
        $Customer = $app['orm.em']
            ->getRepository('Eccube\Entity\Customer')
            ->find($id);

        if (is_null($Customer)) {
            throw new NotFoundHttpException();
        }

        $subject = $app->render('Mail/mail_title.twig', array(
            'title' => '会員登録のご確認',
        ));

        $body = $app->render('Mail/entry_confirm.twig', array(
            'name01' => $Customer->getName01(),
            'name01' => $Customer->getName01(),
            'secretKey' => $Customer->getSecretKey(),
        ));

        $BaseInfo = $app['eccube.repository.base_info']->get();

        $message = $app['mail.message']
            ->setFrom(array($BaseInfo->getEmail03() => $BaseInfo->getShopName()))
            ->setTo(array($Customer->getEmail()))
            ->setBcc($BaseInfo->getEmail01())
            ->setReplyTo($BaseInfo->getEmail03())
            ->setReturnPath($BaseInfo->getEmail04())
            ->setSubject($subject)
            ->setBody($body);
        $app['mailer']->send($message);

        $app->addSuccess('admin.customer.resend.complete', 'admin');

        return $app->redirect($app->url('admin_customer'));
    }

    public function delete(Application $app, Request $request, $id)
    {
        $Customer = $app['orm.em']
            ->getRepository('Eccube\Entity\Customer')
            ->find($id);

        if (is_null($Customer)) {
            throw new NotFoundHttpException();
        }

        $Customer->setDelFlg(1);
        $app['orm.em']->persist($Customer);
        $app['orm.em']->flush();
        $app->addSuccess('admin.customer.delete.complete', 'admin');

        return $app->redirect($app->url('admin_customer'));
    }
}
