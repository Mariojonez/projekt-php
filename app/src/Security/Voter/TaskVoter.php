<?php
/**
 * Task voter.
 */

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TaskVoter.
 */
class TaskVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    private const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    private const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @const string
     */
    private const DELETE = 'DELETE';

    /**
     * Create permission.
     *
     * @const string
     */
    private const CREATE = 'CREATE';

    /**
     * Show permission.
     *
     * @const string
     */
    private const SHOW = 'SHOW';

    /**
     * Edit permission.
     *
     * @const string
     */
    private const EDIT_CATEGORY = 'EDIT_CATEGORY';

    /**
     * Delete permission.
     *
     * @const string
     */
    private const DELETE_CATEGORY = 'DELETE_CATEGORY';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::CREATE, self::SHOW])
            && ($subject instanceof Task || $subject === null || $subject instanceof Category);
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if ($attribute === self::VIEW || $attribute === self::SHOW){
            return true; // Allow everyone to view
        }

        if (!$user instanceof UserInterface) {
            return false; // If the user is not logged in, deny other actions
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::CREATE => $this->canCreate($user),
            self::EDIT_CATEGORY => $this->canEdit($subject, $user),
            self::DELETE_CATEGORY => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can edit task.
     *
     * @param Task          $task Task entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(Task $task, UserInterface $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Checks if user can view task.
     *
     * @param Task          $task Task entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canView(Task $task, UserInterface $user): bool
    {
        return true;
    }

    /**
     * Checks if user can delete task.
     *
     * @param Task          $task Task entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Task $task, UserInterface $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Checks if user can create task.
     *
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canCreate(UserInterface $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Checks if user has admin role.
     *
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function isAdmin(UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles());
    }

    /**
     * Checks if user can delete category.
     *
     * @param Category          $category Category entity
     * @param UserInterface     $user User
     *
     * @return bool Result
     */
    private function canDeleteCategory(Category $category, UserInterface $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Checks if user can edit category.
     *
     * @param Category          $category Category entity
     * @param UserInterface     $user User
     *
     * @return bool Result
     */
    private function canEditCategory(Category $category, UserInterface $user): bool
    {
        return $this->isAdmin($user);
    }
}
