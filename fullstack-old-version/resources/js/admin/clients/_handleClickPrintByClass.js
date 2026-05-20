'use strict'

import { handlePrintError, printElementBySelector } from '../../common/print'

export const handleClickPrintByClass = (offAfterPrint = true) => {
  $('.btn-print').off('click.client-print').on('click.client-print', async function (e) {
    e.preventDefault()

    const modalId = $(this).data('modal_id')
    if (!modalId) {
      handlePrintError(new Error('Không xác định được modal in tem.'))
      return
    }

    try {
      await printElementBySelector({
        selector: `#${modalId} #to-print`,
        title: 'In tem',
        offAfterPrint
      })
    } catch (error) {
      handlePrintError(error)
    }
  })
}
