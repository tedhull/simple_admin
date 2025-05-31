<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCrudController extends AbstractCrudController
{
    private $session;

    public function __construct(private RequestStack $requestStack)
    {
        $this->session = $this->requestStack->getSession();
    }

    public function index(AdminContext $context)
    {
        if ($this->session->get('is_logged_in') == null || !$this->session->get('is_logged_in')) {
            return $this->redirect('/login');
        }
        return parent::index($context);
    }


    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $banUsers = Action::new('ban', 'Ban Selected Users')
            ->linkToCrudAction('banSelectedUsers')
            ->addCssClass('btn btn-danger')
            ->setIcon('fa fa-ban');
        $unbanUsers = Action::new('unban', 'Unban Selected Users')
            ->linkToCrudAction('unbanSelectedUsers')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa fa-check');
        return $actions->addBatchAction($banUsers)
            ->addBatchAction($unbanUsers)->disable(Action::NEW, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            textField::new('username'),
            emailField::new('email'),
            textField::new('status'),
            dateTimeField::new('lastLoginAt'),
        ];
    }

    public function banSelectedUsers(Request $request)
    {
        $users = $request->request->all('batchActionEntityIds');
        $entityManager = $this->container->get('doctrine')->getManagerForClass(User::class);
        foreach ($users as $user) {
            $user = $entityManager->getRepository(User::class)->find($user);
            $user->setStatus('BANNED');
            $entityManager->persist($user);
        }
        $entityManager->flush();
        return $this->redirectToRoute('admin_user_index');
    }

    public function unbanSelectedUsers(Request $request)
    {
        $users = $request->request->all('batchActionEntityIds');
        $entityManager = $this->container->get('doctrine')->getManagerForClass(User::class);
        foreach ($users as $user) {
            $user = $entityManager->getRepository(User::class)->find($user);
            $user->setStatus('ACTIVE');
            $entityManager->persist($user);
        }
        $entityManager->flush();
        return $this->redirectToRoute('admin_user_index');
    }
}
