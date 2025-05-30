<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->addBatchAction(
            Action::new('ban', 'Ban Selected Users')
                ->linkToCrudAction('banSelectedUsers')
                ->setIcon('fa fa-ban')
        );
    }

    public function banSelectedUsers(Request $request)
    {
        $users = $request->request->all('batchActionEntityIds');
        $entityManager = $this->container->get('doctrine')->getManagerForClass(User::class);
        foreach ($users as $user) {
            $user = $entityManager->getRepository(User::class)->find($user);
            $user->setRoles(['ROLE_BANNED']);
            $entityManager->persist($user);
        }
        $entityManager->flush();
        return $this->redirectToRoute('admin_user_index');
    }
}
