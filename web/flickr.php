<?php
class FlickrImages {


    protected $flickr = "https://api.flickr.com/services/feeds/photos_public.gne?tags=landscape&tagmode=any";
    protected $archive = "/tmp/flicker.xml";


    function getContent ( $now = null ){
	if( ! $now ){
	    $now = time();
	}
	$last_day = $now - (24 * 60 * 60);
	if( file_exists( $this->archive ) && filemtime( $this->archive) > $last_day) {
	    return( file_get_contents( $this->archive ) );
	}
	$content = file_get_contents($this->flickr);
	if( $content ){
	    file_put_contents($this->archive,$content);
	    return( $content);
	}
	return( file_get_contents( $this->archive ) );
    }

    function getFeed(){
	return simplexml_load_string($this->getContent());
    }
    function getImageList(){
	
	$feed= $this->getFeed();
	$imageList = array();
	foreach( $feed->entry as $item){
	    $link = (string) $item->link[1]["href"];
	    if( FALSE === strpos( $link, ".jpg")) {
		continue;
	    } 
	    $imageList[] = $link; 
	}
	return $imageList;

    }
}

