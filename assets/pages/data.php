<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ChapterName = $_POST["chaptername"];
    $VideoJson = $_POST["videojson"];
    $NotesJson = $_POST["notejson"];
    $DppJson = $_POST["dppjson"];

    
    $apiUrl = 'https://shahidwalla.cyclic.app/api/batch/-yakeen-2.0-2023-/-botany-(pw-stars)-';
    $subject = 'zoology.html';
    

    $video_data = json_decode($VideoJson, true);
    $note_data = json_decode($NotesJson, true);
    $dpp_data = json_decode($DppJson, true);

    $video_output = [];
    $note_output = [];
    $dpp_output = [];


    //data to json
    
    foreach ($video_data['data'] as $item) {
        $video_output[] = [
            "name" => $item['topic'],
            "image" => $item['videoDetails']['image'],
            "videoUrl" => str_replace(['d1d34p8vz63oiq', 'master.mpd'], ['d26g5bnklkwsh4', 'hls/480/main.m3u8'], $item['videoDetails']['videoUrl']),
            "datetime" => $item['date']
        ];
    }


    usort($video_output, function($a, $b) {
        return strtotime($a['datetime']) - strtotime($b['datetime']);
    });

    header('Content-Type: application/json');
    $final_videojson =  json_encode($video_output, JSON_PRETTY_PRINT| JSON_UNESCAPED_SLASHES);

    // echo $final_videojson;


    // for json notes

    foreach ($note_data['data'] as $item) {

        $name = $item['homeworkIds'][0]['topic'];
        $name = preg_replace('/[^\w\s:<>?\/|().]|(?<=\|{2})/', '', $name);
         $name = str_replace(array (':','||', '|', '?'), '', $name);
          $name = preg_replace('/ +/', ' ', $name);

        // Get other required values
        $baseUrl = $item['homeworkIds'][0]['attachmentIds'][0]['baseUrl'];
        $key = $item['homeworkIds'][0]['attachmentIds'][0]['key'];
        $datetime = $item['date'];

        $note_output[] = [
            "name" => $name . ".pdf",
            "noteUrl" => $baseUrl . $key,
            "datetime" => $datetime,
        ];
    }

    usort($note_output, function($a, $b) {
        return strtotime($a['datetime']) - strtotime($b['datetime']);
    });

    
    $final_notejson = json_encode($note_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    // echo $final_notejson

    //for dpps

    foreach ($dpp_data['data'] as $item) {
        $name = $item['homeworkIds'][0]['topic'];

        $name = preg_replace('/[^\w\s:<>?\/|().]|(?<=\|{2})/', '', $name);
         $name = str_replace(array (':','||', '|', '?'), '', $name);
          $name = preg_replace('/ +/', ' ', $name);

        $baseUrl = $item['homeworkIds'][0]['attachmentIds'][0]['baseUrl'];
        $key = $item['homeworkIds'][0]['attachmentIds'][0]['key'];
        $datetime = $item['date'];


        $dpp_output[] = [
            "name" => $name . ".pdf",
            "dppUrl" => $baseUrl . $key,
            "datetime" => $datetime,
        ];
    }

    usort($dpp_output, function($a, $b) {
        return strtotime($a['datetime']) - strtotime($b['datetime']);
    });

    header('Content-Type: application/json');
    $final_dppjson =  json_encode($dpp_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // echo $final_dppjson;

    ///////////////////////////////////////////////////////////////////////////////////////////


    $chapter_name = json_encode(['name' => $ChapterName]);

    $chapter = curl_init($apiUrl);

    curl_setopt($chapter, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chapter, CURLOPT_POST, true);
    curl_setopt($chapter, CURLOPT_POSTFIELDS, $chapter_name);
    curl_setopt($chapter, CURLOPT_HTTPHEADER, ['Content-Type: application/json',]);

    $api_response = curl_exec($chapter);

    if (curl_errno($chapter)) {
        echo 'Chapter error: ' . curl_error($chapter);
    } else {
        $api_data = json_decode($api_response, true);

        if (isset($api_data['slug'])) {
           $chapterSlug = $api_data['slug'];

        } else {
            echo 'API Response does not contain a slug.';
        }
    }
    curl_close($chapter);


    ////////////////////////////////////////////////////////////////////////////////


    $videourl = $apiUrl."/".$chapterSlug."/videosall";

    $video = curl_init($videourl);

    curl_setopt($video, CURLOPT_POST, 1);
    curl_setopt($video, CURLOPT_POSTFIELDS, $final_videojson);
    curl_setopt($video, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($video, CURLOPT_RETURNTRANSFER, true);

    $api_response = curl_exec($video);

    /////////////////////////////////////////////////////////////////

    $noteurl = $apiUrl."/".$chapterSlug."/notesall";

    $note = curl_init($noteurl);

    curl_setopt($note, CURLOPT_POST, 1);
    curl_setopt($note, CURLOPT_POSTFIELDS, $final_notejson);
    curl_setopt($note, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($note, CURLOPT_RETURNTRANSFER, true);

    $api_response = curl_exec($note);


    /////////////////////////////////////////////////////////////////
    
    $dppurl = $apiUrl."/".$chapterSlug."/dppsall";

    $dpp = curl_init($dppurl);

    curl_setopt($dpp, CURLOPT_POST, 1);
    curl_setopt($dpp, CURLOPT_POSTFIELDS, $final_dppjson);
    curl_setopt($dpp, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($dpp, CURLOPT_RETURNTRANSFER, true);

    $api_response = curl_exec($dpp);


    if(curl_errno($video) | curl_errno($note) | curl_errno($dpp)) {
       echo 'Video Error: '. curl_error($video)."\n";
       echo 'Note Error: '. curl_error($note)."\n";
       echo 'Dpp Error: '. curl_error($dpp);

    } else {
        curl_close($video);
         curl_close($note);
        curl_close($dpp);
        header('Location: ./'.$subject);
    }


    exit; 

} else {
    echo "Invalid request";
}

?>

<img src="" alt="">

