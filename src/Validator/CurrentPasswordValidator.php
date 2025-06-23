<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CurrentPasswordValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CurrentPassword) {
            throw new UnexpectedTypeException($constraint, CurrentPassword::class);
        }

        if (null === $value || '' === $value) {
            return; // handled by NotBlank
        }

        /** @var ?User $user */
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user) {
            return;
        }

        if (!$this->passwordHasher->isPasswordValid($user, $value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
