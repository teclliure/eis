// Get the ul that holds the collection of lines
var collectionHolder = $('table.lines');

// setup an "add a line" link
var $addLineLink = $('#add_line_link');
var $newLinkLi = $('#add_line_link_li');

jQuery(document).ready(function() {
    // add the "add a line" anchor and li to the lines ul
    // collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionHolder.data('index', collectionHolder.find(':input').length);

    // add a delete link to all of the existing tag form li elements
    collectionHolder.find('tr:not(.not_delete)').each(function() {
        addLineFormDeleteLink($(this));
    });

    $addLineLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new line form (see next code block)
        addLineForm(collectionHolder, $newLinkLi);
    });
});

function addLineForm(collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = collectionHolder.data('prototype');

    // get the new index
    var index = collectionHolder.data('index');

    // Replace '$$name$$' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/\$\$name\$\$/g, index);

    // increase the index with one for the next item
    collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a line" link li
    var $newFormLi = $(newForm);
    $newLinkLi.before($newFormLi);

    // add a delete link to the new form
    addLineFormDeleteLink($newFormLi);
}

function addLineFormDeleteLink($lineFormLi) {
    var $removeFormA = $('<a class="delete_line_link" href="#"><i class="icon-remove-circle"></i></a>');
    $lineFormLi.children('.lineDesc').prepend($removeFormA);

    $removeFormA.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // remove the li for the line form
        $lineFormLi.remove();
    });
}