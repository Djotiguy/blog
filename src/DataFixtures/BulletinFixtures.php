<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Bulletin;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class BulletinFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        //Variables dont le but est de renseigner les différents objets à instancier
        $categories = ['Général', 'Divers', 'Urgent'];

        //Nous créons une série de Tag, rangés dans le tableau $tags, avec un name numérique choisi au hasard
        $tags = [];
        for($i=0;$i<15;$i++){
            $tag = new Tag;
            $tag->setName('#' . rand(1000,9999));
            $manager->persist($tag);
            array_push($tags, $tag);
        }

        //Une boucle for qui va instancier nos objets Bulletin, les renseigner, et adresser une demande de persistance pour chacun d'entre eux:
        for($i=0;$i<50;$i++){
            //Nous instancions et renseignons notre objet Bulletin
            $bulletin = new Bulletin;
            $bulletin->setTitle('Bulletin - ' . uniqid());
            $bulletin->setCategory($categories[rand(0, (count($categories) - 1))]);
            $bulletin->setContent($this->generateLorem());
            //On ajoute des Tags à notre bulletin selon une certaine probabilité
            for($j=0;$j<=(count($tags)-1);$j++){
                if(rand(0,10) > 8){
                    //Afin de créer un lien entre le Bulletin et le Tag, il nous suffit simplement de placer le Tag en paramètre de la méthode addTag() de notre Bulletin
                    $bulletin->addTag($tags[$j]);
                }
            }
            //Nous adressons la demande de persistance
            $manager->persist($bulletin);
        }

        $manager->flush();
        //On active cette méthode load() grâce à la commande en terminal
        //php bin/console doctrine:fixtures:load
        //Répondre "yes" à l'avertissement de purge de la base de données
    }

    public function generateLorem(): ?string
    {
        //Cette méthode rend une variation de texte de type Lorem Ipsum
        $loremInitial = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";
        $loremExtraits = [
            "Suspendisse potenti. Nulla semper lacus eu lobortis condimentum. Vivamus ultrices tellus nec eros sodales, ut placerat velit facilisis. Suspendisse ultricies, lacus in elementum pellentesque, ipsum quam lacinia nunc, id mattis diam sem ac risus. Pellentesque lobortis erat at vestibulum dapibus.",
            
            "Suspendisse dictum in leo vel porttitor. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Fusce facilisis quam leo, vitae dignissim metus sagittis sed. Nam volutpat sapien porttitor est dapibus pulvinar. Aliquam efficitur lacus ut lorem lobortis pretium.",
            
            "Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed vulputate ante in molestie dictum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vivamus est nulla, varius sit amet varius at, dapibus ut elit. Aliquam finibus nunc id ante consequat placerat.",
            
            "Nulla iaculis hendrerit bibendum. Cras suscipit lacinia tincidunt. Donec in magna ac eros congue dapibus. Ut accumsan nec sapien eu tristique. Aliquam erat volutpat. Praesent massa est, tristique a dui vel, cursus iaculis sem.",
            
            "Integer eu feugiat purus. Proin in lorem sem. Mauris rutrum mi nec velit imperdiet, sit amet pharetra est bibendum. Sed nec bibendum nunc. Duis laoreet pellentesque venenatis. Etiam efficitur euismod ultrices. Vestibulum pretium sollicitudin facilisis.
            
            Aliquam vehicula commodo sapien ut ultrices. Aliquam sit amet mi vitae augue lobortis ornare. Cras tincidunt, erat quis euismod varius, massa nisl tempor ex, ut tincidunt arcu lectus et tortor. Donec sagittis viverra cursus.",

            "Duis consequat orci quis bibendum malesuada. Ut cursus, elit sed rutrum malesuada, libero velit fermentum enim, vitae consectetur velit nibh sit amet arcu. Duis ut purus pellentesque, consectetur metus sit amet, pellentesque tellus.",
        ];
        //Nous définissons une variable $lorem qui commencer par le même extrait de Lorem Ipsum avant d'ajouter plusieurs autres extraits au hasard
        $lorem = $loremInitial; //Notre lorem commence par l'extrait initial
        for($i=0;$i<rand(2,5);$i++){
            if(rand(0,10) > 8){
                $lorem .= "
                
                "; //Retour à la ligne optionnel
            }
            $lorem .= $loremExtraits[rand(0, (count($loremExtraits) - 1) )];
        }
        return $lorem;
    }

}
