<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\PasswordHasher\Command\UserPasswordHashCommand;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin1=new User();
        $admin1->setEmail('admin@gmail.com');
        $admin1->setPassword($this->hasher->hashPassword($admin1,'admin'));
        $admin1->setRoles(['ROLE_ADMIN']);
        $admin1->setNom("admin");
        $admin1->setCin("12345678");
        $admin1->setPrenom("admin");
        $admin1->setTel("12345667");
        $d=new \DateTime("1998-03-20");
        $admin1->setDateNais($d);
        $admin2=new User();
        $admin2->setEmail('admin2@gmail.com');
        $admin2->setPassword($this->hasher->hashPassword($admin2,'admin'));
        $admin2->setRoles(['ROLE_ADMIN']);
        $admin2->setNom("admin");
        $admin2->setCin("12345678");
        $admin2->setPrenom("admin");
        $admin2->setTel("12345667");
        $admin2->setDateNais($d);
        $manager->persist($admin1);
        $manager->persist($admin2);
        for ($i=1;$i<5;$i++){
            $user=new User();
            $user->setEmail("user$i@gmail.com");
            $user->setPassword($this->hasher->hashPassword($user,'user'));
            $user->setNom("user");
            $user->setCin("12345678");
            $user->setPrenom("user");
            $user->setTel("12345667");
            $user->setDateNais($d);
            $manager->persist($user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
    return ['user'];
    }
}
