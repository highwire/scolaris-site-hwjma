/* ---------------- Article Print : JCOREX-342 ---------------- */
(function ($) {
    $('.action-printer').click(function() {
        // Get all data based on classes
        var article_subtitle = $('.article__subtitle').html();
        var article_title = $('.article__title').html();
        var article_author_data = document.getElementsByClassName("tooltip-author");
        var article_author = [].map.call( article_author_data, function(node){
            return node.textContent || node.innerText || "";
        }).join("");
        var article_doi = $('.journal-doi-info').html();
        var article_content = $('.article__tabcontent-body').html();
        // Print article data from variables
        var print_area = window.open();
        print_area.document.write( '<div><p><h5>'+ article_subtitle +'</h5></p></div><div><p><h3>' + article_title +'</h3></p></div><div>' + article_author + '</div><div>' + article_doi + '</div><div><p>' + article_content + '</p></div>');
        print_area.document.close();
        print_area.focus();
        print_area.print();
        print_area.close();
    });
}(jQuery));
/* ---------------- Article Print : JCOREX-342 ---------------- */
