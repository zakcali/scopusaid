<!DOCTYPE html>
<!-- scopusaid V1.4: bu yazılım Dr. Zafer Akçalı tarafından oluşturulmuştur 
programmed by Zafer Akçalı, MD -->
<html>
<script src="https://cdn.jsdelivr.net/g/filesaver.js"></script> 
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>aid numarasından yayınları getir</title>
</head>

<body>
<?php
set_time_limit(60); 
$authorId=$authorOrcid=$ad=$soyad=$yayinlar='';
$sayi=0;
$sidDizi = array ();
if (isset($_POST['aid'])) {
	$gelenId=trim($_POST['aid']);
	$authorId=preg_replace("/[^0-9]/", "", $gelenId); // Sadece rakamlar
	if(($authorId)!=''){
		yazarBilgisiAl();
$yayinlar = "ScopusId\t"."Pub type\t"."Source\t"."Year\t"."Journal/Book Name\t"."issn\t"."eissn\t"."isbn\t"."Title\t"."Vol.\t"."Issue\t"."doi\t"."PMID\t"."Page.S\t"."Page.E\t"."Auth.#\t"."Authors\n";
	for ($i=0; $i>-1; $i=$i+200) {
		$yayinDizi=yayinlariAl($i,200);
		$sayi=(int)$yayinDizi['search-results']['opensearch:totalResults'];
//		echo ' i ve sayı='.$i.' '.$sayi; // for debugging
		yayinlariYaz($yayinDizi);
		if ($i+200>=$sayi)
			break; // yayınların hepsini aldın, çık
	}

	}
}

function yayinlariYaz($dizi) {
	global $yayinlar, $sidDizi;
	$n=0;

foreach ($dizi['search-results']['entry'] as $eleman=>$yayin) {
	$sidDizi[$n]['scopusid']=$sidDizi[$n]['PublicationType']=$sidDizi[$n]['PublicationAccess']=$sidDizi[$n]['Year']=$sidDizi[$n]['dergi']=$sidDizi[$n]['ISSN']=$sidDizi[$n]['eISSN']=$sidDizi[$n]['ISBN']=$sidDizi[$n]['ArticleTitle']=$sidDizi[$n]['Volume']=$sidDizi[$n]['Issue']=$sidDizi[$n]['doi']=$sidDizi[$n]['PMID']=$sidDizi[$n]['StartPage']=$sidDizi[$n]['EndPage']=$sidDizi[$n]['yazarS']=$sidDizi[$n]['yazarlar']='';
	$yazarlar='';
	$yazarS=0;
	
	$sidDizi[$n]['scopusid'] = '2-s2.0-'.str_replace('SCOPUS_ID:','',$yayin['dc:identifier']); // scopus eid numarası
	if (isset ($yayin['subtypeDescription']))  			// Yayın türü
		$sidDizi[$n]['PublicationType']=$yayin['subtypeDescription'];
	if (isset ($yayin['prism:aggregationType']))		// yayın erişimi
		$sidDizi[$n]['PublicationAccess']=$yayin['prism:aggregationType'];
	$sidDizi[$n]['Year']=substr ($yayin['prism:coverDate'],0,4);		// basım yılı
	$sidDizi[$n]['dergi']=$yayin['prism:publicationName'];				// dergi ismi
	if (isset ($yayin['prism:issn'])) {					// issn numarası
		$issntext=$yayin['prism:issn'];
		$sidDizi[$n]['ISSN']=substr ($issntext,0,4).'-'.substr ($issntext,4,4);
		}
	if (isset ($yayin['prism:eIssn'])) {				// eissn numarası
		$eIssntext=$yayin['prism:eIssn'];
		$sidDizi[$n]['eISSN']=substr ($eIssntext,0,4).'-'.substr ($eIssntext,4,4);
		}
	if (isset ($yayin['prism:isbn'][0]))			// isbn numaraları, kitaplar için
		$sidDizi[$n]['ISBN']=$ISBN.$yayin['prism:isbn'][0]['$'];
	if (isset ($yayin['prism:isbn'][1]))
		$sidDizi[$n]['ISBN']=$ISBN.'; '.$yayin['prism:isbn'][1]['$'];
	if (isset($yayin['dc:title']))					// makalenin başlığı
		$sidDizi[$n]['ArticleTitle']=$yayin['dc:title'];
	if (isset($yayin['prism:volume']))				// Cilt
		$sidDizi[$n]['Volume']=$yayin['prism:volume'];
	if (isset($yayin['prism:issueIdentifier']))		// sayı
		$sidDizi[$n]['Issue']=$yayin['prism:issueIdentifier'];
	if (isset($yayin['prism:doi']))					// doi bilgisi
		$doi=$sidDizi[$n]['doi']=$yayin['prism:doi'];
	if (isset($yayin['pubmed-id']))					// PMID, pubmed-id numarası
		$sidDizi[$n]['PMID']= $yayin['pubmed-id'];
	if (isset($yayin['prism:pageRange'])) {			// başlangıç-bitiş sayfası
		$sayfalar=explode ("-", $yayin['prism:pageRange']);
		$sidDizi[$n]['StartPage']= $sayfalar[0];					// başlangıç sayfası
		if (isset($sayfalar[1]))
			$sidDizi[$n]['EndPage']=$sayfalar[1];					// bitiş sayfası
		}
	if (isset($yayin['article-number']))
		$sidDizi[$n]['StartPage']=$yayin['article-number'];
		
	foreach ($yayin['author'] as $el) { // yazarlar
		$isim=$soyisim='';
		if (isset($el['surname']))
			$soyisim=$el['surname'];
		if (isset($el['given-name']))
			$isim=$el['given-name'];
		$yazarlar=$yazarlar.$isim." ".$soyisim.", ";
		$yazarS=$yazarS+1;					// yazar sayısı
		}
	$sidDizi[$n]['yazarS']=$yazarS;
	$sidDizi[$n]['yazarlar']=substr ($yazarlar,0,-2); // son yazardan sonraki virgül ve boşluğu sil
	
	$yayinlar=$yayinlar.$sidDizi[$n]['scopusid']."\t".$sidDizi [$n]['PublicationType']."\t".$sidDizi[$n]['PublicationAccess']."\t".$sidDizi[$n]['Year']."\t".$sidDizi[$n]['dergi']."\t".$sidDizi[$n]['ISSN']."\t".$sidDizi[$n]['eISSN']."\t".$sidDizi[$n]['ISBN']."\t".$sidDizi[$n]['ArticleTitle']."\t".$sidDizi[$n]['Volume']."\t".$sidDizi[$n]['Issue']."\t".$sidDizi[$n]['doi']."\t".$sidDizi[$n]['PMID']."\t".$sidDizi[$n]['StartPage']."\t".$sidDizi[$n]['EndPage']."\t".$sidDizi[$n]['yazarS']."\t".$sidDizi[$n]['yazarlar']."\n";
	}
// echo $yayinlar;
}

function yazarBilgisiAl() {
global $authorId,$authorOrcid,$ad, $soyad;
$preText='https://api.elsevier.com/content/author?author_id=';
// https://dev.elsevier.com/sc_author_retrieval_views.html
$postText='&view=light'; 
$url = $preText.$authorId.$postText;
$proxy = 'proxy.xx.xx.xx:xxxx';
$proxyauth = 'xxxx:xxxx';

$ch = curl_init();
// curl_setopt($ch, CURLOPT_PROXY, $proxy);
// curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'X-ELS-APIKey: your_api_key'));
$data=curl_exec($ch);
curl_close($ch);
$scopusBilgi=(json_decode($data, true));
// print_r ($scopusBilgi);
if (isset($scopusBilgi['author-retrieval-response'][0]['coredata']['orcid']))
	$authorOrcid=$scopusBilgi['author-retrieval-response'][0]['coredata']['orcid'];
if (isset($scopusBilgi['author-retrieval-response'][0]['preferred-name']['given-name']))
	$ad=$scopusBilgi['author-retrieval-response'][0]['preferred-name']['given-name'];
if (isset($scopusBilgi['author-retrieval-response'][0]['preferred-name']['surname']))
	$soyad=$scopusBilgi['author-retrieval-response'][0]['preferred-name']['surname'];
}

function yayinlariAl($ilk,$adet) {
global $authorId;
$preText='https://api.elsevier.com/content/search/scopus?query=AU-ID(';
$postText=')&field=dc:identifier,subtypeDescription,prism:coverDate,prism:publicationName,prism:issn,prism:isbn,prism:doi,dc:title,prism:volume,prism:issueIdentifier,prism:pageRange,article-number,author,pubmed-id,prism:aggregationType&start='.$ilk.'&count='.$adet; // &count=4 &count=200 (max)
$url = $preText.$authorId.$postText;
// echo ($url);
//echo ("<br>");

$proxy = 'proxy.xx.xx.xx:xxxx';
$proxyauth = 'xxxx:xxxx';

$ch = curl_init();
// curl_setopt($ch, CURLOPT_PROXY, $proxy);
// curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'X-ELS-APIKey: your_api_key'));
$data=curl_exec($ch);
curl_close($ch);
$scopusBilgi=(json_decode($data, true));
// print_r ($scopusBilgi);
return ($scopusBilgi);
}
?>
<a href="Scopus aid nerede.png" target="_blank"> Scopus AuthorId nereden bakılır? </a>
<form method="post" action="">
Scopus aid  giriniz<br/>
<input type="text" name="aid" id="aid" value="<?php echo $authorId;?>" >
<input type="submit" value="Yazar bilgilerini PHP ile getir">
</form>
<button id="scopusGoster" onclick="scopusGoster()">Scopus profil sayfasını göster</button>
<button id="scopusAtifGoster" onclick="scopusAtifGoster()">Scopus atıflarını göster</button> <br>
<button id="saveTxtBtn" onclick="saveTxtFunction()">csv olarak kaydet</button><br> 
Ad: <input type="text" name="ad" size="16"  id="ad" value="<?php echo $ad;?>"> 
Soyad: <input type="text" name="soyad" size="20"  id="soyad" value="<?php echo $soyad;?>"> 
ORCID: <input type="text" name="ORCID" size="20"  id="ORCID" value="<?php echo $authorOrcid;?>"><br>
AuthorId: <input type="text" name="said" size="12"  id="said" value="<?php echo $authorId;?>"> 
Yayın sayısı: <input type="text" name="sayi" size="6"  id="sayi" value="<?php echo $sayi;?>"> <br>
Yayınlar<br>
<textarea rows = "20" cols = "90" name = "yayinlar"  wrap="off" id="yayinlar"><?php echo $yayinlar;?></textarea>  <br>
<script>
function saveTxtFunction() {
var blob = new Blob([document.getElementById('yayinlar').value],
                { type: "text/plain;charset=utf-8" });
saveAs(blob, "output.csv");
}
function scopusGoster() {
var	w=document.getElementById('aid').value.replace(" ","");
	urlText = 'http://www.scopus.com/authid/detail.uri?origin=resultslist&authorId='+w;
	window.open(urlText,"_blank");
}
function scopusAtifGoster() {
var	w=document.getElementById('aid').value.replace(" ","");
	urlText = 'https://www.scopus.com/hirsch/author.uri?accessor=authorProfile&auidList='+w+'&origin=AuthorProfile&display=hIndex';
	window.open(urlText,"_blank");
}
</script>
</body>
