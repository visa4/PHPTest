<?php
namespace Application\Controller;

use  Application\Entity\Product;
use Application\Entity\Tag;
use Application\Form\ProductForm;
use Doctrine\ORM\PersistentCollection;
use ImgMan\Image\Image;
use ImgMan\Image\ImageInterface;
use ImgMan\Service\ImageServiceInterface;
use Zend\Http\Header\ContentLength;
use Zend\Http\Header\ContentType;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    /**
     * @var DoctrineORMEntityManager
     */
    protected $em;

    protected function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    public function indexAction()
    {
        return new ViewModel([
            'products' => $this->getEntityManager()->getRepository('Application\Entity\Product')->findAll(),
        ]);
    }

    public function createAction()
    {
        $formManager = $this->serviceLocator->get('FormElementManager');
        /* @var $form \Zend\Form\Form */
        $form = $formManager->get('Application\Form\ProductForm');
        $view  = new ViewModel;
        $view->setVariable('isPost', false);

        if ($this->getRequest()->isPost()){
            $view->setVariable('isPost', true);
            $post = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($post);
            if (isset($post['tags'])) {
                $view->setVariable('tags', $post['tags']);
            }

            if ($form->isValid() && $this->isValidTags(@$post['tags'])) {

                /** @var $imgManService ImageServiceInterface */
                $product = new Product();
                $product->exchangeArray($this->getRequest()->getPost());
                $this->getEntityManager()->persist($product);
                $this->getEntityManager()->flush();

                $imgManService = $this->getServiceLocator()->get('ImgMan\Service\ProductImage');
                $image = new Image($post['image']['tmp_name']);
                $identifier = $imgManService->grab($image, 'product-image-' . $product->id);

                $product->image = $identifier;
                $this->getEntityManager()->persist($product);
                $this->getEntityManager()->flush();

                /** @var $tag PersistentCollection */
                $tags = $product->tags;
                foreach ($post['tags'] as $tagString) {

                    $tag = new Tag();
                    $tag->name = $tagString;
                    $tag->product = $product;
                    $tags->add($tag);
                }

                $this->getEntityManager()->merge($product);
                $this->getEntityManager()->flush();

                return $this->redirect()->toRoute('product/list');
            }
        }

        return $view->setVariable('form', $form);
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('product_id', 0);
        /** @var $product Product */
        $product = $this->getEntityManager()->find('Application\Entity\Product', $id);
        if (!$product) {
            return $this->notFoundAction();
        }
        $view  = new ViewModel;
        $view->setVariable('tags', $product->tags);
        $view->setVariable('isPost', false);

        $formManager = $this->serviceLocator->get('FormElementManager');
        /* @var $form \Zend\Form\Form */
        $form = $formManager->get('Application\Form\ProductForm');
        $input = $form->getInputFilter()->get('image');
        $input->setRequired(false);
        $form->setData($product->getArrayCopy());
        $success = false;

        if ($this->getRequest()->isPost()){

            $post = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $view->setVariable('isPost', true);
            $form->setData($post);
            $view->setVariable('tags', $post['tags']);

            if ($form->isValid() && $this->isValidTags($post['tags'])) {

                if (isset($post['image']) && !empty($post['image']['tmp_name'])) {

                    $imgManService = $this->getServiceLocator()->get('ImgMan\Service\ProductImage');
                    $image = new Image($post['image']['tmp_name']);
                    $identifier = $imgManService->grab($image, 'product-image-' . $product->id);
                    $product->image = $identifier;

                }

                $product->exchangeArray($this->getRequest()->getPost());
                /** @var $tag PersistentCollection */
                $tags = $product->tags;

                foreach ($tags as $tag) {
                    $this->getEntityManager()->remove($tag);
                }

                $tags->clear();
                foreach ($post['tags'] as $tagString) {

                    $tag = new Tag();
                    $tag->name = $tagString;
                    $tag->product = $product;
                    $tags->add($tag);
                }

                $this->getEntityManager()->flush();
                $success = true;
            }
        }

        return $view->setVariables([
            'form' => $form,
            'product' => $product,
            'success' => $success
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('product_id', 0);
        /** @var $product Product */
        $product = $this->getEntityManager()->find('Application\Entity\Product', $id);
        if (!$product) {
            return $this->notFoundAction();
        }

        $this->getEntityManager()->remove($product);
        $this->getEntityManager()->flush();

        return $this->redirect()->toRoute('product/list');
    }

    public function imageAction()
    {
        $id = (int) $this->params()->fromRoute('product_id', 0);
        $rendition = $this->params()->fromQuery('rendition', 'normal');
        /** @var $product Product */
        $product = $this->getEntityManager()->find('Application\Entity\Product', $id);
        if (!$product && !$product->image) {
            return $this->notFoundAction();
        }

        $imgManService = $this->getServiceLocator()->get('ImgMan\Service\ProductImage');
        $image = $imgManService->get($product->image, $rendition);

        if (!$image) {
            return $this->notFoundAction();
        }

        $response = new Response();
        $response->setContent($image->getBlob());
        $response->getHeaders()->addHeader(new ContentLength(strlen($image->getBlob())));
        $response->getHeaders()->addHeader(new ContentType($image->getMimeType()));
        return $response;
    }

    protected function isValidtags($tags)
    {
        if (count($tags) == 0) {
            return false;
        }
        foreach($tags as $tag) {
            if (empty($tag)) {
                return false;
            }
        }

        return true;
    }
}
