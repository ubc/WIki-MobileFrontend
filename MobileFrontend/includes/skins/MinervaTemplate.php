<?php
class MinervaTemplate extends BaseTemplate {
	/**
	 * @var Boolean
	 */
	protected $isSpecialPage;

	public function getPersonalTools() {
		return $this->data['personal_urls'];
	}

	public function execute() {
		$this->isSpecialPage = $this->getSkin()->getTitle()->isSpecialPage();
		wfRunHooks( 'MinervaPreRender', array( $this ) );
		$this->render( $this->data );
	}

	public function getLanguageVariants() {
		return $this->data['content_navigation']['variants'];
	}

	public function getLanguages() {
		return $this->data['language_urls'];
	}

	public function getDiscoveryTools() {
		return $this->data['discovery_urls'];
	}

	public function getSiteLinks() {
		return $this->data['site_urls'];
	}

	public function getPageActions() {
		return $this->data['page_actions'];
	}

	public function getFooterLinks( $option = null ) {
		return $this->data['footerlinks'];
	}

	protected function renderFooter( $data ) {
		if ( !$data['disableSearchAndFooter'] ) {
		?>
		<div id="footer">
			<?php
				foreach( $this->getFooterLinks() as $category => $links ):
			?>
				<ul class="footer-<?php echo $category; ?>">
					<?php
						foreach( $links as $link ) {
							if ( isset( $this->data[$link] ) ) {
								echo Html::openElement( 'li', array( 'id' => "footer-{$category}-{$link}" ) );
								$this->html( $link );
								echo Html::closeElement( 'li' );
							}
						}
					?>
				</ul>
			<?php
				endforeach;
			?>
		</div>
		<?php
		}
	}

	protected function renderPageActions( $data ) {
		?><ul id="page-actions" class="hlist"><?php
		foreach( $this->getPageActions() as $key => $val ):
			echo $this->makeListItem( $key, $val );
		endforeach;
		?></ul><?php
	}

	/**
	 * Outputs the 'Last edited' message, e.g. 'Last edited on...'
	 * @param Array $data Data used to build the page
	 */
	protected function renderHistoryLink( $data ) {
		if ( isset( $data['historyLink'] ) ) {
			$historyLink = $data['historyLink'];
			$historyLabel = $historyLink['text'];
			unset( $historyLink['text'] );
			echo Html::element( 'a', $historyLink, $historyLabel );
		}
	}

	/**
	 * Renders history link at top of page
	 * @param Array $data Data used to build the page
	 */
	protected function renderHistoryLinkBottom( $data ) {
		$this->renderHistoryLink( $data );
	}

	protected function renderMetaSections() {
		echo Html::openElement( 'div', array( 'id' => 'page-secondary-actions' ) );

		// If languages are available, render a languages link
		if ( $this->getLanguages() || $this->getLanguageVariants() ) {
			$languageUrl = SpecialPage::getTitleFor(
				'MobileLanguages',
				$this->getSkin()->getTitle()
			)->getLocalURL();
			$languageLabel = wfMessage( 'mobile-frontend-language-article-heading' )->text();

			echo Html::element( 'a', array(
				'class' => 'mw-ui-button mw-ui-progressive button languageSelector',
				'href' => $languageUrl
			), $languageLabel );
		}

		echo Html::closeElement( 'div' );
	}

	/**
	 * Renders the content of a page
	 * @param Array $data Data used to build the page
	 */
	protected function renderContent( $data ) {
		if ( !$data[ 'unstyledContent' ] ) {
			echo Html::openElement( 'div', array(
				'id' => 'content',
				'class' => 'content',
				'lang' => $data['pageLang'],
				'dir' => $data['pageDir'],
			) );
			?>
			<?php
				if ( isset( $data['subject-page'] ) ) {
					echo $data['subject-page'];
				}
				echo $data[ 'bodytext' ];
				$this->renderMetaSections();
				$this->renderHistoryLinkBottom( $data );
			?>
		</div>
		<?php
		} else {
			echo $data[ 'bodytext' ];
		}
	}

	protected function renderPreContent( $data ) {
		$internalBanner = $data[ 'internalBanner' ];
		$isSpecialPage = $this->isSpecialPage;
		$preBodyText = isset( $data['prebodytext'] ) ? $data['prebodytext'] : '';

		if ( $internalBanner || $preBodyText ) {
		?>
		<div class="pre-content">
			<?php
				echo $preBodyText;
				// FIXME: Temporary solution until we have design
				if ( isset( $data['_old_revision_warning'] ) ) {
					echo $data['_old_revision_warning'];
				} elseif ( !$isSpecialPage ){
					$this->renderPageActions( $data );
				}
				echo $internalBanner;
				?>
		</div>
		<?php
		}
	}

	protected function renderContentWrapper( $data ) {
		?>
		<script>
			if ( window.mw && mw.mobileFrontend ) { mw.mobileFrontend.emit( 'header-loaded' ); }
		</script>
		<?php
			$this->renderPreContent( $data );
			$this->renderContent( $data );
	}

	protected function renderMainMenu( $data ) {
		?>
		<ul>
		<?php
		foreach( $this->getDiscoveryTools() as $key => $val ):
			echo $this->makeListItem( $key, $val );
		endforeach;
		?>
		</ul>
		<ul>
		<?php
		foreach( $this->getPersonalTools() as $key => $val ):
			echo $this->makeListItem( $key, $val );
		endforeach;
		?>
		</ul>
		<ul class="hlist">
		<?php
		foreach( $this->getSiteLinks() as $key => $val ):
			echo $this->makeListItem( $key, $val );
		endforeach;
		?>
		</ul>
		<?php
	}

	protected function render( $data ) { // FIXME: replace with template engines

		// begin rendering
		echo $data[ 'headelement' ];
		?>
		<div id="mw-mf-viewport">
			<div id="mw-mf-page-left" class="navigation-drawer">
				<?php
					$this->renderMainMenu( $data );
				?>
			</div>
			<div id='mw-mf-page-center'>
				<?php
					foreach( $this->data['banners'] as $banner ):
						echo $banner;
					endforeach;
				?>
    <div class="ubc-header-container">
    <!-- UBC Global Utility Menu -->
    <div class="collapse expand" id="ubc7-global-menu">
        <div id="ubc7-search" class="expand">
        	<div class="container">
	            <div id="ubc7-search-box">
	                <form class="form-search" method="get" action="http://www.ubc.ca/search/refine/" role="search">
	                    <input type="text" name="q" placeholder="Search UBC websites" class="input-xlarge search-query">
	                    <input type="hidden" name="label" value="Search UBC" />
	                    <input type="hidden" name="site" value="*.ubc.ca" />
	                    <button type="submit" class="btn">Search</button>
	                </form>
	            </div>
			</div>
        </div>
        <div class="container">
	        <div id="ubc7-global-header" class="expand">
	            <!-- Global Utility Header from CDN -->
	        </div>
        </div>
    </div>
    <!-- End of UBC Global Utility Menu -->

    <!-- UBC Header -->
    <header id="ubc7-header" class="row-fluid expand" role="banner">
    	<div class="container">
	        <div class="span1">
	            <div id="ubc7-logo">
	                <a href="http://www.ubc.ca">The University of British Columbia</a>
	            </div>
	        </div>
	        <div class="span2">
	            <div id="ubc7-apom">
	                <a href="//cdn.ubc.ca/clf/ref/aplaceofmind">UBC - A Place of Mind</a>                        
	            </div>
	        </div>
	        <div class="span9" id="ubc7-wordmark-block">
	            <div id="ubc7-wordmark">
	                <a href="http://www.ubc.ca">The University of British Columbia</a>
	            </div>
	            <div id="ubc7-global-utility">
	                <button type="button" data-toggle="collapse" data-target="#ubc7-global-menu"><span>UBC Search</span></button>
	                <noscript><a id="ubc7-global-utility-no-script" href="http://www.ubc.ca/">UBC Search</a></noscript>
	            </div>
	        </div>
		</div>
    </header>
    <!-- End of UBC Header -->

    <!-- UBC Unit Identifier -->
    <div id="ubc7-unit" class="row-fluid expand">
    	<div class="container">
	        <div class="span12">
	            <!-- Mobile Menu Icon -->
	            <div class="navbar">
	                <a class="btn btn-navbar" data-toggle="collapse" data-target="#ubc7-unit-navigation">
	                    <span class="icon-bar"></span>
	                    <span class="icon-bar"></span>
	                    <span class="icon-bar"></span>
	                </a>
	            </div>
	            <!-- Read more about Unit Name Treatment on http://brand.ubc.ca/clf -->
	            <!-- No Faculty Treatment --><!--<div id="ubc7-unit-name" class="ubc7-single-element"> -->
	            <div id="ubc7-unit-name">
	                <a href="/"><span id="ubc7-unit-faculty">Faculty Name</span><span id="ubc7-unit-identifier">UBC Wiki</span></a>
	            </div>
	        </div>
		</div>
    </div>
    <!-- End of UBC Unit Identifier -->
</div>


				<div class="header">
					<?php
						$this->html( 'menuButton' );
						if ( $data['disableSearchAndFooter'] ) {
							echo $data['specialPageHeader'];
						} else {
							?>
							<form action="<?php echo $data['wgScript'] ?>" class="search-box">
							<?php
							echo $this->makeSearchInput( $data['searchBox'] );
							// FIXME: change this into a search icon instead of a text button
							echo $this->makeSearchButton(
								'fulltext',
								array( 'class' => 'searchSubmit mw-ui-button mw-ui-progressive' )
							);
							?>
							</form>
							<?php
						}
						echo $data['secondaryButton'];
					?>
				</div>
				<div id="content_wrapper">
				<?php
					$this->renderContentWrapper( $data );
				?>
				</div>
				<?php
					$this->renderFooter( $data );
				?>
			</div>
		</div>
		<?php
			echo $data['reporttime'];
			echo $data['bottomscripts'];
		?>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		 <script src="//cdn.ubc.ca/clf/7.0.4/js/ubc-clf.min.js"></script>
		</body>
		</html>
		<?php
	}
}
