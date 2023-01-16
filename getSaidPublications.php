<?php
set_time_limit(60); 

class getSaidPublications {
	private $n; // said dizisinin satır sayısı
	function __construct() {
		$this->initialize();
		}
	function initialize () {
		$this->authorId=''; $this->authorOrcid=''; $this->ad=''; $this->soyad=''; $this->yayinlar=''; $this->yayinS=''; $this->atifS=''; $this->shindex=''; $this->dikkat=''; 
		$this->sidDizi = array (); // scopus id bilinen bir kişinin yayınları
		$this->sayi=0; $this->n=0;
		}
		
	final function saidPublication ($said) {
	$this->initialize();
	$yazar=$this->yazarBilgisiAl($said); // true veya false
	if (!$yazar) {
		return;
		}
	$this->yayinlar = "ScopusId\t"."Pub type\t"."Source\t"."Year\t"."Journal/Book Name\t"."issn\t"."eissn\t"."isbn\t"."Title\t"."Vol.\t"."Issue\t"."doi\t"."PMID\t"."Page.S\t"."Page.E\t"."Auth.#\t"."Authors\n";
	for ($i=0; $i>-1; $i=$i+200) {
		$yayinDizi=$this->yayinlariAl($said,$i,200);
		$this->sayi=(int)$yayinDizi['search-results']['opensearch:totalResults'];
//		echo ' i ve sayı='.$i.' '.$this->sayi; // for debugging
		$this->yayinlariYaz($yayinDizi);
			if ($i+200>=$this->sayi)
				break; // yayınların hepsini aldın, çık
			}
//	var_dump($this->sidDizi); // debug için
}	// final function saidPublication 
	
	private function yazarBilgisiAl($id) {
	$preText='https://api.elsevier.com/content/author?author_id=';
// https://dev.elsevier.com/sc_author_retrieval_views.html
	$postText='&view=enhanced'; // basic, metrics, light, standard, enhanced
	$url = $preText.$id.$postText;
	$proxy = 'proxy.a.edu.b:c';
	$proxyauth = 'xx:yy';
	$ch = curl_init();
// curl_setopt($ch, CURLOPT_PROXY, $proxy);
// curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'X-ELS-APIKey: your-API-KEY'));
	$data=curl_exec($ch);
	curl_close($ch);

	$scopusBilgi=(json_decode($data, true));
 // print_r ($scopusBilgi);
	if ( isset ($scopusBilgi['error-response'])) {
		$this->dikkat = 'siteye bağlanamadı'; // message:Forbidden
		return false;	}
	if ( isset ($scopusBilgi['service-error'])) {
		$this->dikkat = 'siteye bağlanamadı'; //  AUTHORIZATION_ERROR
		return false;	}
	if (!isset($scopusBilgi['author-retrieval-response'][0]['coredata']['eid'])) { // böyle bir yazar yok 
		$this->dikkat = 'yazar bulunamadı';
		return false;
	}
	$this->authorId=$id;
	if (isset($scopusBilgi['author-retrieval-response'][0]['coredata']['orcid']))
		$this->authorOrcid=$scopusBilgi['author-retrieval-response'][0]['coredata']['orcid'];
	if (isset($scopusBilgi['author-retrieval-response'][0]['coredata']['document-count']))
		$this->yayinS=$scopusBilgi['author-retrieval-response'][0]['coredata']['document-count'];
	if (isset($scopusBilgi['author-retrieval-response'][0]['coredata']['cited-by-count']))
		$this->atifS=$scopusBilgi['author-retrieval-response'][0]['coredata']['cited-by-count'];
	if (isset($scopusBilgi['author-retrieval-response'][0]['h-index'])) // enhanced or metrics view
		$this->shindex=$scopusBilgi['author-retrieval-response'][0]['h-index'];
	if (isset($scopusBilgi['author-retrieval-response'][0]['author-profile']['preferred-name']['given-name']))
		$this->ad=$scopusBilgi['author-retrieval-response'][0]['author-profile']['preferred-name']['given-name'];
	if (isset($scopusBilgi['author-retrieval-response'][0]['author-profile']['preferred-name']['surname']))
		$this->soyad=$scopusBilgi['author-retrieval-response'][0]['author-profile']['preferred-name']['surname'];
	return true;	
	}
	
	private function yayinlariAl($id,$ilk,$adet) {
	$preText='https://api.elsevier.com/content/search/scopus?query=AU-ID(';
	$postText=')&field=dc:identifier,subtypeDescription,prism:coverDate,prism:publicationName,prism:issn,prism:isbn,prism:doi,dc:title,prism:volume,prism:issueIdentifier,prism:pageRange,article-number,author,pubmed-id,prism:aggregationType&start='.$ilk.'&count='.$adet; // &count=4 &count=200 (max)
	$url = $preText.$id.$postText;
// echo ($url);
//echo ("<br>");

	$proxy = 'proxy.a.b.c:d';
	$proxyauth = 'xx:yy';
	$ch = curl_init();
 // curl_setopt($ch, CURLOPT_PROXY, $proxy);
 // curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'X-ELS-APIKey: your-API-KEY'));
	$data=curl_exec($ch);
	curl_close($ch);
	$scopusBilgi=(json_decode($data, true));
//print_r ($scopusBilgi);
	return ($scopusBilgi);
	}
	
	private function yayinlariYaz($dizi) {

	foreach ($dizi['search-results']['entry'] as $eleman=>$yayin) {
		$this->sidDizi[$this->n]['scopusid']=$this->sidDizi[$this->n]['PublicationType']=$this->sidDizi[$this->n]['PublicationAccess']=$this->sidDizi[$this->n]['Year']=$this->sidDizi[$this->n]['dergi']=$this->sidDizi[$this->n]['ISSN']=$this->sidDizi[$this->n]['eISSN']=$this->sidDizi[$this->n]['ISBN']=$this->sidDizi[$this->n]['ArticleTitle']=$this->sidDizi[$this->n]['Volume']=$this->sidDizi[$this->n]['Issue']=$this->sidDizi[$this->n]['doi']=$this->sidDizi[$this->n]['PMID']=$this->sidDizi[$this->n]['StartPage']=$this->sidDizi[$this->n]['EndPage']=$this->sidDizi[$this->n]['yazarS']=$this->sidDizi[$this->n]['yazarlar']='';
		$yazarlar='';
		$yazarS=0;
	
		$this->sidDizi[$this->n]['scopusid'] = '2-s2.0-'.str_replace('SCOPUS_ID:','',$yayin['dc:identifier']); // scopus eid numarası
		if (isset ($yayin['subtypeDescription']))  			// Yayın türü
			$this->sidDizi[$this->n]['PublicationType']=$yayin['subtypeDescription'];
		if (isset ($yayin['prism:aggregationType']))		// yayın erişimi
			$this->sidDizi[$this->n]['PublicationAccess']=$yayin['prism:aggregationType'];
		$this->sidDizi[$this->n]['Year']=substr ($yayin['prism:coverDate'],0,4);		// basım yılı
		$this->sidDizi[$this->n]['dergi']=$yayin['prism:publicationName'];				// dergi ismi
		if (isset ($yayin['prism:issn'])) {					// issn numarası
			$issntext=$yayin['prism:issn'];
			$this->sidDizi[$this->n]['ISSN']=substr ($issntext,0,4).'-'.substr ($issntext,4,4);
			}
		if (isset ($yayin['prism:eIssn'])) {				// eissn numarası
			$eIssntext=$yayin['prism:eIssn'];
			$this->sidDizi[$this->n]['eISSN']=substr ($eIssntext,0,4).'-'.substr ($eIssntext,4,4);
			}
		if (isset ($yayin['prism:isbn'][0]))			// isbn numaraları, kitaplar için
			$this->sidDizi[$this->n]['ISBN'].=$yayin['prism:isbn'][0]['$'];
		if (isset ($yayin['prism:isbn'][1]))
			$this->sidDizi[$this->n]['ISBN'].='; '.$yayin['prism:isbn'][1]['$'];
		if (isset($yayin['dc:title']))					// makalenin başlığı
			$this->sidDizi[$this->n]['ArticleTitle']=$yayin['dc:title'];
		if (isset($yayin['prism:volume']))				// Cilt
			$this->sidDizi[$this->n]['Volume']=$yayin['prism:volume'];
		if (isset($yayin['prism:issueIdentifier']))		// sayı
			$this->sidDizi[$this->n]['Issue']=$yayin['prism:issueIdentifier'];
		if (isset($yayin['prism:doi']))					// doi bilgisi
			$this->sidDizi[$this->n]['doi']=$yayin['prism:doi'];
		if (isset($yayin['pubmed-id']))					// PMID, pubmed-id numarası
			$this->sidDizi[$this->n]['PMID']= $yayin['pubmed-id'];
		if (isset($yayin['prism:pageRange'])) {			// başlangıç-bitiş sayfası
			$sayfalar=explode ("-", $yayin['prism:pageRange']);
			$this->sidDizi[$this->n]['StartPage']= $sayfalar[0];					// başlangıç sayfası
			if (isset($sayfalar[1]))
				$this->sidDizi[$this->n]['EndPage']=$sayfalar[1];					// bitiş sayfası
			}
		if (isset($yayin['article-number']))
			$this->sidDizi[$this->n]['StartPage']=$yayin['article-number'];
		
		foreach ($yayin['author'] as $el) { // yazarlar
			$isim=$soyisim='';
			if (isset($el['surname']))
				$soyisim=$el['surname'];
			if (isset($el['given-name']))
				$isim=$el['given-name'];
		$yazarlar=$yazarlar.$isim." ".$soyisim.", ";
		$yazarS=$yazarS+1;					// yazar sayısı
		}
		$this->sidDizi[$this->n]['yazarS']=$yazarS;
		$this->sidDizi[$this->n]['yazarlar']=substr ($yazarlar,0,-2); // son yazardan sonraki virgül ve boşluğu sil
	
	$this->yayinlar.=$this->sidDizi[$this->n]['scopusid']."\t".$this->sidDizi [$this->n]['PublicationType']."\t".$this->sidDizi[$this->n]['PublicationAccess']."\t".$this->sidDizi[$this->n]['Year']."\t".$this->sidDizi[$this->n]['dergi']."\t".$this->sidDizi[$this->n]['ISSN']."\t".$this->sidDizi[$this->n]['eISSN']."\t".$this->sidDizi[$this->n]['ISBN']."\t".$this->sidDizi[$this->n]['ArticleTitle']."\t".$this->sidDizi[$this->n]['Volume']."\t".$this->sidDizi[$this->n]['Issue']."\t".$this->sidDizi[$this->n]['doi']."\t".$this->sidDizi[$this->n]['PMID']."\t".$this->sidDizi[$this->n]['StartPage']."\t".$this->sidDizi[$this->n]['EndPage']."\t".$this->sidDizi[$this->n]['yazarS']."\t".$this->sidDizi[$this->n]['yazarlar']."\n";
	$this->n+=1;
		}
// echo $this->yayinlar;
	}
}
