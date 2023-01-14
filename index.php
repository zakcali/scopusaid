<!DOCTYPE html>
<!-- scopusaid V2.5: bu yazılım Dr. Zafer Akçalı tarafından oluşturulmuştur 
programmed by Zafer Akçalı, MD added: enhanced view, h-index, changed name, surname position in array-->
<html>
<script src="https://cdn.jsdelivr.net/g/filesaver.js"></script> 
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>aid numarasından yayınları getir</title>
</head>

<body>
<?php
set_time_limit(60); 

require_once 'getSaidPublications.php';
$sa=new getSaidPublications ();
if (isset($_POST['aid'])) {
	$gelenId=preg_replace("/[^0-9]/", "", $_POST['aid']); // Sadece rakamlar
	if ($gelenId !== '')
		$sa->saidPublication ($gelenId);	
// print_r ($sa->sidDizi);
}

?>
<a href="Scopus aid nerede.png" target="_blank"> Scopus AuthorId nereden bakılır? </a>
<form method="post" action="">
Scopus aid  giriniz. <?php echo ' '.$sa->dikkat;?><br/>
<input type="text" name="aid" id="aid" value="<?php echo $sa->authorId;?>" >
<input type="submit" value="Yazar bilgilerini PHP ile getir">
</form>
<button id="scopusGoster" onclick="scopusGoster()">Scopus profil sayfasını göster</button>
<button id="scopusAtifGoster" onclick="scopusAtifGoster()">Scopus atıflarını göster</button> <br>
<button id="saveTxtBtn" onclick="saveTxtFunction()">csv olarak kaydet</button><br> 
Ad: <input type="text" name="ad" size="16"  id="ad" value="<?php echo $sa->ad;?>"> 
Soyad: <input type="text" name="soyad" size="20"  id="soyad" value="<?php echo $sa->soyad;?>"> 
ORCID: <input type="text" name="ORCID" size="20"  id="ORCID" value="<?php echo $sa->authorOrcid;?>"><br>
AuthorId: <input type="text" name="said" size="12"  id="said" value="<?php echo $sa->authorId;?>">  
h-index: <input type="text" name="hindex" size="2"  id="hindex" value="<?php echo $sa->hindex;?>"> 
yayın sayısı: <input type="text" name="yayins" size="4"  id="yayins" value="<?php echo $sa->yayinS;?>"> 
atif sayısı: <input type="text" name="atifs" size="4"  id="atifs" value="<?php echo $sa->atifS;?>"> <br>
Listelenen yayın sayısı: <input type="text" name="sayi" size="6"  id="sayi" value="<?php echo $sa->sayi;?>">
Yayınlar<br>
<textarea rows = "20" cols = "90" name = "yayinlar"  wrap="off" id="yayinlar"><?php echo $sa->yayinlar;?></textarea>  <br>
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
