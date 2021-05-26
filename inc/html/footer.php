    </main>
    <?php
        //bbcode
        if(isset($loadBBcode) && isset($BBcodeEditorID) && isset($BBcodeEditorHeight) && $loadBBcode === true) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sceditor@3/minified/sceditor.min.js"></script>
            <script src="bbcode/languages/cs.js"></script>
            <script src="bbcode/icons/material.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sceditor@3/minified/formats/bbcode.min.js"></script>
            <script>
                let textarea = document.getElementById("'.$BBcodeEditorID.'");
                sceditor.create(textarea, {
                    format: "bbcode",
                    style: "https://cdn.jsdelivr.net/npm/sceditor@3/minified/themes/content/default.min.css",
                    emoticonsRoot: "bbcode/",
                    resizeEnabled: false,
                    width: "100%",
                    height: '.$BBcodeEditorHeight.',
                    icons: "material",
                    fonts: "Arial,Georgia,Impact,Sans-serif,Serif,Verdana",
                    toolbarExclude: "table,bulletlist,orderedlist,horizontalrule,ltr,rtl",
                    locale: "cs"
                });
            </script>';
        }
        //js na profilu
        if(isset($loadProfileJS) && $loadProfileJS === true) {
            echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
                  <script src="js/profile.js"></script>';
        }
    ?>
</body>
</html>