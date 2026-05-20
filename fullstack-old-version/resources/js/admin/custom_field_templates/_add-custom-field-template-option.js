;

export const handleAddRowOption = () => {
  $('.btn-add-option').on('click', function (e) {
    e.preventDefault();

    const $originalRow = $(this).closest('.add-option');
    const $newRow = $originalRow.clone(true);

    // Remove the add button from the original row
    $originalRow.find('.btn-add-option').remove();
    $originalRow.find('.btn-remove-option').removeClass('d-none');

    // Increment the index in the cloned row
    $newRow.find('input').each(function() {
      const nameAttr = $(this).attr('name');
      const regex = /options\[(\d+)\]\[(key|value)\]/;
      const match = nameAttr.match(regex);

      if (match) {
        const oldIndex = parseInt(match[1]);
        const fieldType = match[2];
        const newIndex = oldIndex + 1;
        $(this).attr('name', `options[${newIndex}][${fieldType}]`);
      }

      $(this).val(''); // Clear the values in the cloned row
    });

    // No need to assign DOM ids; removal is done via closest row.

    // Append the new row after the original row
    $originalRow.after($newRow);
  })
}
