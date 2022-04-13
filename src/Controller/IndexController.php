<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Entity\Bulletin;
use App\Form\BulletinType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        //Méthode de la page d'accueil, affiche la liste des bulletins

        //Afin de pouvoir dialoguer avec notre base de données et récupérer les éléments qui nous intéressent, nous avons besoin de l'Entity Manager et du Repository de Bulletin
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //Nous récupérons la liste des Catégories
        $categories = $bulletinRepository->findEachCategory();
        //Nous récupérons la totalité de nos Bulletins (inversé du plus récent au plus ancien):
        $bulletins = array_reverse($bulletinRepository->findAll());
        
        //Nous transmettons notre variable bulletinS vers la page Twig désirée
        return $this->render('index/index.html.twig', [
            'categories' => $categories,
            'bulletins' => $bulletins,
        ]);
    }

    #[Route('/category/{categoryName}', name: 'index_category')]
    public function indexCategory(string $categoryName, ManagerRegistry $doctrine): Response
    {
        //Cette méthode publie la liste des différents bulletins associés à une Catégorie donnée, dont le nom est indiqué dans notre URL via le paramètre de route

        //Afin de pouvoir dialoguer avec notre base de données et récupérer les éléments qui nous intéressent, nous avons besoin de l'Entity Manager et du Repository de Bulletin
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //Nous récupérons la liste des Catégories
        $categories = $bulletinRepository->findEachCategory();
        //Nous récupérons à présent les différents Bulletins dont la valeur de $category correspond à celle indiquée dans l'URL
        $bulletins = $bulletinRepository->findBy(['category' => $categoryName], ['id' => 'DESC']);
        //Si la liste des résultats au sein de $bulletins est vide, nous retournons à la page d'accueil
        if(empty($bulletins)){
            return $this->redirectToRoute('app_index');
        }

        //Nous transmettons la liste des Bulletins vers la page Twig désirée:
        return $this->render('index/index.html.twig', [
            'categoryName' => $categoryName,
            'categories' => $categories,
            'bulletins' => $bulletins,
        ]);
    }

    #[Route('/bulletins/tag/{tagId}', name: 'index_tag')]
    public function indexTag(int $tagId, ManagerRegistry $doctrine): Response
    {
        //Cette méthode affiche tous les Bulletins liés à un Tag dont l'ID est renseigné dans notre URL

        //Nous récupérons l'Entity Manager et le Repository de Tag, afin de récupérer le Tag qui nous intéresse
        $entityManager = $doctrine->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        //Nous récupérons la liste des Catégories
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        $categories = $bulletinRepository->findEachCategory();
        //Nous récupérons le Tag dont l'ID a été noté dans l'URL. Si la recherche n'aboutit pas; nous retournons à l'index
        $tag = $tagRepository->find($tagId);
        if(!$tag){
            return $this->redirectToRoute('app_index');
        }
        //Si nous trouvons le Tag correspondant à l'ID, nous publions la liste des Bulletins qui lui sont associés
        $bulletins = $tag->getBulletins();
        //Transmission à notre page index.html.twig
        return $this->render('index/index.html.twig', [
            'categories' => $categories,
            'bulletins' => $bulletins,
        ]);
    }

    #[Route('/cheatsheet', name: 'index_cheatsheet')]
    public function cheatsheet(ManagerRegistry $doctrine): Response
    {
        //Nous récupérons la liste des Catégories
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        $categories = $bulletinRepository->findEachCategory();
        return $this->render('index/cheatsheet.html.twig', [
            'categories' => $categories,
            'cheatsheet_var' => true,
        ]);
    }

    #[Route('/taglist', name: 'tag_display')]
    public function displayTags(ManagerRegistry $doctrine): Response
    {
        //Cette méthode nous présente la liste des Tags enregistrés sous la forme d'une liste dans un fichier twig prévu à cet effet
    
        //Afin de récupérer nos éléments de notre base de données, nous avons besoin de l'Entity Manager et du Repository de Tag
        $entityManager = $doctrine->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        //Nous récupérons la liste des Catégories
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        $categories = $bulletinRepository->findEachCategory();
        //Nous récupérons TOUS les Tags:
        $tags = $tagRepository->findAll();
        //Nous transmettons la liste des Tags à notre fichier twig taglist
        return $this->render('index/taglist.html.twig', [
            'displaytags_var' => true,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    #[Route('/bulletin/display/{bulletinId}', name: 'bulletin_display')]
    public function displayBulletin(int $bulletinId, ManagerRegistry $doctrine): Response
    {
        //Cette méthode affiche le contenu d'un Bulletin dont l'ID a été renseigné au sein de notre URL

        //Afin de pouvoir récupérer notre Bulletin de notre base de données, nous avons besoin de l'Entity Manager ainsi que du Repository de Bulletin
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //Nous récupérons la liste des Catégories
        $categories = $bulletinRepository->findEachCategory();
        //Si nous ne retrouvons pas notre Bulletin, nous retournons à l'index
        $bulletin = $bulletinRepository->find($bulletinId);
        if(!$bulletin){
            return $this->redirectToRoute('app_index');
        }
        //Maintenant que nous possédons notre bulletin, nous l'envoyons vers index.html.twig
        return $this->render('index/index.html.twig', [
            'categories' => $categories,
            'bulletins' => [$bulletin],
        ]);
    }

    #[Route('/tag/create', name: 'tag_create')]
    public function createTag(Request $request, ManagerRegistry $doctrine): Response
    {
        //Cette méthode présente un formulaire de création de Tag à l'utilisateur, afin de pouvoir persister une Entity renseignée via ses champs

        //Afin de pouvoir faire persister une Entity, nous avons besoin de l'Entity Manager
        $entityManager = $doctrine->getManager();
        //Nous récupérons la liste des Catégories
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        $categories = $bulletinRepository->findEachCategory();
        //Nous créons un nouvel objet Tag, que nous lions à notre formulaire TagType
        $tag = new Tag;
        $tagForm = $this->createForm(TagType::class, $tag);
        //Nous appliquons la requête à notre formulaire
        $tagForm->handleRequest($request);
        //Si notre formulaire est rempli et valide, nous le persistons avant de revenir à l'accueil
        if($tagForm->isSubmitted() && $tagForm->isValid()){
            $entityManager->persist($tag);
            $entityManager->flush();
            return $this->redirectToRoute('app_index');
        }
        //Si notre formulaire n'est pas valide, nous le présentons à l'Utilisateur:
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Création de Tag',
            'dataForm' => $tagForm->createView(),
         ]);
    }

    #[Route('/delete-tag/{tagId}', name: 'tag_delete')]
    public function deleteTag(int $tagId, ManagerRegistry $doctrine): Response
    {
        //Cette méthode supprimer un Tag de notre base de données, renseigné par son ID via notre URL

        //Afin de pouvoir récupérer le Tag nécessaire, nous avons besoin de l'Entity Manager et du Repository Tag
        $entityManager = $doctrine->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        //Nous recherchons le Tag à supprimer via la méthode find() du Repository. Si celle-ci rend null (le tagId est invalide), nous retournons sur /taglist
        $tag = $tagRepository->find($tagId);
        if(!$tag){
            return $this->redirectToRoute('tag_display');
        }
        //Si nous possédons le tag, nous procédons à sa suppression via la méthode remove() de l'Entity Manager
        $entityManager->remove($tag);
        $entityManager->flush();
        //Une fois que la suppression à été effectuée, nous revenons à notre liste de Tags
        return $this->redirectToRoute('tag_display');
    }

    #[Route('/bulletin/create', name: 'bulletin_create')]
    public function createBulletin(Request $request, ManagerRegistry $doctrine): Response
    {
        //Cette méthode présente un formulaire de création de Bulletin à l'utilisateur, afin de pouvoir persister une Entité renseignée via ses champs

        //Pour persister une Entity, nous avons besoin de l'Entity Manager
        $entityManager = $doctrine->getManager();
        //Nous récupérons la liste des Catégories
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        $categories = $bulletinRepository->findEachCategory();
        //Nous créons une instance de Bulletin que nous lions à notre formulaire BulletinType
        $bulletin = new Bulletin;
        $bulletinForm = $this->createForm(BulletinType::class, $bulletin);
        //Nous plaçons les informations de notre Request au sein de notre formulaire
        $bulletinForm->handleRequest($request);
        //Si notre formulaire est rempli et valide, nous le persistons:
        if($bulletinForm->isSubmitted() && $bulletinForm->isValid()){
            $entityManager->persist($bulletin);
            $entityManager->flush();
            //La persistance terminée, on retourne à l'Index
            return $this->redirectToRoute('app_index');
        }
        //Si le formulaire n'est pas rempli, nous le présentons à l'Utilisateur
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Création de Bulletin',
            'dataForm' => $bulletinForm->createView(), //CreateView() prépare à l'affichage
        ]);
    }

    #[Route('/bulletin/update/{bulletinId}', name: 'bulletin_update')]
    public function updateBulletin(int $bulletinId, Request $request, ManagerRegistry $doctrine): Response
    {
        //Cette méthode nous permet de modifier un bulletin dont l'ID est indiqué dans la barre de navigation via un formulaire

        //Afin de pouvoir récupérer des éléments de notre base de données, nous avons besoin de l'Entity Manager et du Repository pertinent (BulletinRepository)
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //Nous récupérons le Bulletin dont l'ID est indiqué par notre URL. Si ce dernier est introuvable, nous retournons à l'index
        $bulletin = $bulletinRepository->find($bulletinId);
        if(!$bulletin){
            return $this->redirectToRoute('app_index');
        }
        //Si nous possédons notre bulletin, nous pouvons le prendre en charge une fois lié à un formulaire
        $bulletinForm = $this->createForm(BulletinType::class, $bulletin);
        //On applique l'élement Request sur notre bulletin, si notre formulaire est rempli et validé, nous le persistons:
        $bulletinForm->handleRequest($request);
        if($bulletinForm->isSubmitted() && $bulletinForm->isValid()){
            //Si notre bulletin est rempli et valide, nous le persistons
            $entityManager->persist($bulletin);
            $entityManager->flush();
            //Nous retournons à l'index
            return $this->redirectToRoute('app_index');
        }
        //Si le bulletin n'est pas rempli, nous le présentons à l'utilisateur:
        return $this->render('index/dataform.html.twig', [
            'formName' => 'Modification de Bulletin',
            'dataForm' => $bulletinForm->createView(),
        ]);
    }

    #[Route('/bulletin/delete/{bulletinId}', name: 'bulletin_delete')]
    public function deleteBulletin(int $bulletinId, ManagerRegistry $doctrine): Response
    {  
        //Cette route a pour objectif de supprimer un Bulletin dont l'ID est indiqué dans notre URL.

        //Afin de récupérer le Bulletin et de le supprimer, nous avons besoin de l'Entity Manager ainsi que du Repository de Bulletin
        $entityManager = $doctrine->getManager();
        $bulletinRepository = $entityManager->getRepository(Bulletin::class);
        //Nous recherchons le Bulletin selon l'ID présentée. Si le Bulletin n'est pas trouvé, nous retournons à l'index
        $bulletin = $bulletinRepository->find($bulletinId);
        if(!$bulletin){
            return $this->redirectToRoute('app_index');
        }
        //Maintenant que nous possédons notre Bulletin, nous procédons à sa suppression avant de revenir à l'Index
        //Avant de procéder à la suppression du Bulletin, nous devons retirer tous ses liens avec les Tags associés:
        if($bulletin->getTags()){
            foreach($bulletin->getTags() as $tag){
                //Chaque tag de notre tableau de tags sera retiré via la méthode removeTag()
                $bulletin->removeTag($tag);
                $entityManager->persist($bulletin);
            }
            $entityManager->flush(); //On enregistre le Bulletin sous sa forme libre de tags
        }
        $entityManager->remove($bulletin);
        $entityManager->flush();
        return $this->redirectToRoute('app_index');
    }

    #[Route('/bulletin/generate', name:'bulletin_generate')]
    public function generateBulletin(ManagerRegistry $doctrine): Response
    {
        //Cette méthode génère automatiquement un Bulletin au sein de notre Base de Données lorsqu'appelée

        //Afin de dialoguer avec notre base de données, nous avons de l'Entity Manager
        $entityManager = $doctrine->getManager();
        //Variable temporaire $lorem pour simplifier le renseignement de notre objet
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ornare molestie orci ut luctus. Curabitur enim nunc, vestibulum in molestie a, porta ut nisl. Duis vehicula nunc vel aliquam vulputate. Vivamus tincidunt massa eros.
        Nec feugiat lacus finibus ac. Aliquam sed molestie mauris. Vivamus malesuada risus nisi, id iaculis felis posuere at. Proin accumsan luctus sapien, in finibus lorem feugiat vel. Curabitur vitae ipsum mi. Nullam quis quam non quam interdum tristique vel nec metus.';
        //Nous créons et renseignons notre objet Bulletin
        $bulletin = new Bulletin;
        $bulletin->setTitle('Bulletin #' . rand(100,999));
        $bulletin->setCategory('Général');
        $bulletin->setContent($lorem);
        //Nous plaçons une demande de persistance que nous confirmons ensuite
        $entityManager->persist($bulletin);
        $entityManager->flush();
        //Une fois que notre Bulletin est envoyé, nous retournons à l'index grâce à la méthode RedirectToRoute
        return $this->redirectToRoute('app_index');
    }

    #[Route('/display/{ticket}', name: 'response_display')]
    public function responseDisplay(int $ticket = 0): Response
    {
        //Cette méthode affiche en réponse un <div> dont la couleur est déterminée par la valeur numérique passée dans l'URL à la place de {ticket}. Cette valeur est récupérée et traitée dans le corps de notre méthode de Controller pour déterminer la valeur la chaîne de caractères à envoyer en Response à l'Utilisateur.

        //Corps de la fonction.
        switch($ticket){
            case 0:
                $color = "black";
                break;
            case 1:
                $color = "blue";
                break;
            case 2:
                $color = "red";
                break;
            case 3:
                $color = "green";
                break;
            case 4:
                $color = "yellow";
                break;
            default:
                $color = "gray";
        }
        //return
        return new Response('<div style="width: 500px; height: 500px; background-color: ' . $color . '"></div>');
    }
}
