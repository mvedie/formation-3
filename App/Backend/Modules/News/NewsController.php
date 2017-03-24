<?php
/**
 * Created by PhpStorm.
 * User: gstenek
 * Date: 28/02/2017
 * Time: 16:30
 */

namespace App\Backend\Modules\News;

use Entity\Newc;
use Entity\Newg;
use FormBuilder\NewsFormBuilder;
use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \Entity\News;
use \Entity\Commentc;
use \OCFram\FormHandler;
use FormBuilder\CommentFormBuilder;

class NewsController extends BackController
{
	public function executeIndex(HTTPRequest $request)
	{
		$this->page->addVar('title', 'Gestion des news');
		
		$manager = $this->managers->getManagerOf('Newc');
		
		$this->page->addVar('listeNews', $manager->getNewsListUsingNNE(-1,-1,Newc::NNE_VALID));
		$this->page->addVar('nombreNews', $manager->countNewcUsingNNE(Newc::NNE_VALID));
	}
	
	public function executeBuildNews( HTTPRequest $request ) {
		
			if($request->getExists( 'id' )){ // L'identifiant de la news est transmis si on veut la modifier
				
				if ( $request->postData( 'submit' ) ) {
					
					$Newg = new Newg( [ 'fk_NNC' => $request->getData( 'id' ) ] );
					$this->executePutNews( $request, $Newg );
					
				}
				$this->page->addVar( 'submit', 'Valider' );
				$this->page->addVar( 'action', '' );
				$this->page->addVar( 'title', 'Edition d\'une news' );
				
				$Newc = $this->managers->getManagerOf( 'Newc' )->getNewcUsingNewcId( $request->getData( 'id' ) );

				$Newg = $this->managers->getManagerOf( 'Newg' )->getNewgValidUsingNewcId($Newc->id());

				$Memberc = $this->managers->getManagerOf( 'Memberc' )->getMembercUsingId($Newg->fk_MMC());
				
				
				
				if($Newg){
					$formBuilder = new NewsFormBuilder($Newg);
					$formBuilder->build();
					
					$form = $formBuilder->form();
					
					$infos = 'Dernière édition le '.$Newg->date_edition().' par '.$Memberc->login();
					$this->page->addVar( 'infos', $infos );
					
					$this->page->addVar( 'form', $form->createView() );
				}else{  // lien incorrect
					// redirect et message au user
					$this->app->user()->setFlash('News introuvable !');
					$this->app->httpResponse()->redirect('/');
				}
				
			}else{ // Sinon on ajoute une novuelle news
				
				if ( $request->postData( 'submit' ) ) { // si le formulaire a été validée
					$this->executePutNews( $request, new Newg() );
				}
				else { // Sinon on construit le formulaire
					$this->page->addVar( 'title', 'Ajout d\'une news' );
					
					$formBuilder = new NewsFormBuilder( new Newg() );
					$formBuilder->build();
					
					$form = $formBuilder->form();
					
					$this->page->addVar( 'form', $form->createView() );
					$this->page->addVar( 'submit', 'Valider' );
					$this->page->addVar( 'action', '' );
					
				}
			}
	}
	
	private function executePutNews(HTTPRequest $request, Newg $Newg) {
		
		$Newg->setTitle($request->postData('title'));
		$Newg->setContent($request->postData('content'));
		$Newg->setFk_MMC($this->app()->user()->getAttribute('Memberc')->Id());
		$Newg->setFk_NNE(Newg::NNE_VALID);
		$Newg->setDate_edition(date("Y-m-d H:i:s"));
		
		$formBuilder = new NewsFormBuilder($Newg);
		$formBuilder->build();
		$form = $formBuilder->form();
		
		if(!($Newg->fk_NNC() === NULL)) // Si on édite une news
		{
			// vérifier si le contenu a changé
			$Newg_to_compare = $this->managers->getManagerOf( 'Newg' )->getNewgValidUsingNewcId($Newg->fk_NNC());
			if($Newg->isEqual($Newg_to_compare))
			{
				$this->app->user()->setFlash('Vous n\'avez pas modifier la news');
			}else{
				$form->entity()->setId($Newg_to_compare->id());
				// On récupère le gestionnaire de formulaire (le paramètre de getManagerOf() est bien entendu à remplacer).
				$formHandler = new FormHandler($form, $this->managers->getManagerOf('Newg'), $request);
				
				if ($formHandler->process())
				{
					$this->app->user()->setFlash('News bien modifée !');
					$this->app->httpResponse()->redirect( '/admin/' );
				}
			}
			
		}else{ //si on ajoute une nouvelle news
			
			// On récupère le gestionnaire de formulaire (le paramètre de getManagerOf() est bien entendu à remplacer).
			$formHandler = new FormHandler($form, $this->managers->getManagerOf('Newg'), $request);
			
			if ($formHandler->process())
			{
				$this->app->user()->setFlash('News bien ajoutée !');
				$this->app->httpResponse()->redirect( '/admin/' );
			}
		}
		
		$this->page->addVar('title', 'Ajout d\'une news');
		$this->page->addVar('form', $form->createView());
		$this->page->addVar('submit', 'Valider');
		$this->page->addVar('action', '');
		
	}
	
	public function executeBuildCommentForm(HTTPRequest $request)
	{
		if($request->getExists( 'id' )){ // L'identifiant duc om est transmis si on veut le modifier
			
			if ( $request->postData( 'submit' ) ) {
				
				$Commentc = new Commentc( [ 'NCC_id' => $request->getData( 'id' ) ] );
				$this->executePutCommentc( $request, $Commentc );
				
			}else{
				$this->page->addVar( 'submit', 'Valider' );
				$this->page->addVar( 'action', '' );
				$this->page->addVar( 'title', 'Modification d\'un commentaire' );
				
				$Commentc = $this->managers->getManagerOf( 'Commentc' )->getCommentcUsingCommentcId( $request->getData( 'id' ) );
				
				if($Commentc){
					$formBuilder = new CommentFormBuilder($Commentc, $this);
					$formBuilder->build();
					
					$form = $formBuilder->form();
					
					//$infos = 'Dernière édition le '.$Newg->date_edition().' par '.$Memberc->login();
					//$this->page->addVar( 'infos', $infos );
					
					$this->page->addVar( 'form', $form->createView() );
				}else{  // lien incorrect
					// redirect et message au user
					$this->app->user()->setFlash('Commentaire introuvable !');
					$this->app->httpResponse()->redirect('/');
				}
			}
		}
		
	}
	
	public function executePutCommentc(HTTPRequest $request, Commentc $Commentc){
		
		// setters
		$Commentc->setContent($request->postData('content'));
		
		$formBuilder = new CommentFormBuilder($Commentc, $this);
		$formBuilder->build();
		$form = $formBuilder->form();
		
		// vérifier si le contenu a changé
		$Commentc_to_compare = $this->managers->getManagerOf( 'Commentc' )->getCommentcUsingCommentcId($Commentc->id());
		if($Commentc->isEqual($Commentc_to_compare))
		{
			$this->app->user()->setFlash('Vous n\'avez pas modifier la news');
		}else{
			//$form->entity()->setId($Commentc_to_compare->id());
			$form->entity()->setFk_MMC($Commentc_to_compare->fk_MMC());
			$form->entity()->setFk_NCE($Commentc_to_compare->fk_NCE());
			$form->entity()->setFk_NNG($Commentc_to_compare->fk_NNG());
			$form->entity()->setVisitor($Commentc_to_compare->visitor());
			$form->entity()->setDate($Commentc_to_compare->date());
			// On récupère le gestionnaire de formulaire (le paramètre de getManagerOf() est bien entendu à remplacer).
			$formHandler = new FormHandler($form, $this->managers->getManagerOf('Commentc'), $request);
			
			if ($formHandler->process())
			{
				$this->app->user()->setFlash('Commentaire  bien modifé !');
				//$this->app->httpResponse()->redirect( '/admin/' );
				$Newg =  $this->managers->getManagerOf('Newg')->getNewgUsingNewgId($form->entity()->fk_NNG());
				$this->app->httpResponse()->redirect('/news-'.$Newg->fk_NNC().'.html');
			}
		}
		
		$this->page->addVar('title', 'Modification  d\'un commentaire');
		$this->page->addVar('form', $form->createView());
		$this->page->addVar('submit', 'Valider');
		$this->page->addVar('action', '');
	}
	
	public function executeClearNews(HTTPRequest $request)
	{
		$newsId = $request->getData('id');
		
		$this->managers->getManagerOf('Newc')->updatefk_NNEOfNewcUsingNewcIdAndNNE($newsId, Newc::NNE_INVALID);
		//$this->managers->getManagerOf('Comments')->deleteFromNews($newsId);
		
		$this->app->user()->setFlash('La news a bien été supprimée !');
		
		$this->app->httpResponse()->redirect('.');
	}
	
	
	public function executeClearComment(HTTPRequest $request)
	{
		$Commentc = $this->managers->getManagerOf('Commentc')->getCommentcUsingCommentcId($request->getData('id'));
		$Commentc->setFk_NCE(Commentc::NCE_INVALID);
		$this->managers->getManagerOf('Commentc')->save($Commentc);
		$this->app->user()->setFlash('Le commentaire a bien été supprimé !');
		
		$Newg =  $this->managers->getManagerOf('Newg')->getNewgUsingNewgId($Commentc->fk_NNG());
		$this->app->httpResponse()->redirect('/news-'.$Newg->fk_NNC().'.html');
	}
	
	
}