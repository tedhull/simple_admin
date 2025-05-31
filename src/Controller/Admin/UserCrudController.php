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
        $userIds = $request->request->all('batchActionEntityIds');
        $entityManager = $this->container->get('doctrine')->getManagerForClass(User::class);

        $bannedCount = 0;
        $alreadyBannedCount = 0;
        $bannedUsernames = [];

        foreach ($userIds as $userId) {
            $user = $entityManager->getRepository(User::class)->find($userId);

            if ($user) {
                if ($user->getStatus() !== 'BANNED') {
                    $user->setStatus('BANNED');
                    $entityManager->persist($user);
                    $bannedCount++;
                    $bannedUsernames[] = $user->getUsername();
                } else {
                    $alreadyBannedCount++;
                }
            }
        }

        $entityManager->flush();

        // Add flash messages based on results
        if ($bannedCount > 0) {
            $message = sprintf(
                '%d user%s successfully banned: %s',
                $bannedCount,
                $bannedCount > 1 ? 's' : '',
                implode(', ', $bannedUsernames)
            );
            $this->addFlash('success', $message);
        }

        if ($alreadyBannedCount > 0) {
            $this->addFlash('warning', sprintf(
                '%d user%s %s already banned',
                $alreadyBannedCount,
                $alreadyBannedCount > 1 ? 's' : '',
                $alreadyBannedCount > 1 ? 'were' : 'was'
            ));
        }

        if ($bannedCount === 0 && $alreadyBannedCount === 0) {
            $this->addFlash('error', 'No users were processed');
        }

        return $this->redirectToRoute('admin_user_index');
    }

    public function unbanSelectedUsers(Request $request)
    {
        $userIds = $request->request->all('batchActionEntityIds');
        $entityManager = $this->container->get('doctrine')->getManagerForClass(User::class);

        $unbannedCount = 0;
        $alreadyActiveCount = 0;
        $unbannedUsernames = [];

        foreach ($userIds as $userId) {
            $user = $entityManager->getRepository(User::class)->find($userId);

            if ($user) {
                if ($user->getStatus() !== 'ACTIVE') {
                    $user->setStatus('ACTIVE');
                    $entityManager->persist($user);
                    $unbannedCount++;
                    $unbannedUsernames[] = $user->getUsername();
                } else {
                    $alreadyActiveCount++;
                }
            }
        }

        $entityManager->flush();

        // Add flash messages based on results
        if ($unbannedCount > 0) {
            $message = sprintf(
                '%d user%s successfully unbanned: %s',
                $unbannedCount,
                $unbannedCount > 1 ? 's' : '',
                implode(', ', $unbannedUsernames)
            );
            $this->addFlash('success', $message);
        }

        if ($alreadyActiveCount > 0) {
            $this->addFlash('warning', sprintf(
                '%d user%s %s already active',
                $alreadyActiveCount,
                $alreadyActiveCount > 1 ? 's' : '',
                $alreadyActiveCount > 1 ? 'were' : 'was'
            ));
        }

        if ($unbannedCount === 0 && $alreadyActiveCount === 0) {
            $this->addFlash('error', 'No users were processed');
        }

        return $this->redirectToRoute('admin_user_index');
    }
}
