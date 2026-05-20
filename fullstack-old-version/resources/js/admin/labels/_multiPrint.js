'use strict'

import { handlePrintError, printElementById } from '../../common/print'

export const handleClickMultiPrint = (offAfterPrint = true) => {
  $('#btn-multi-print').off('click.label-multi-print').on('click.label-multi-print', async function (e) {
    e.preventDefault()

    try {
      await printElementById({
        elementId: 'multi-print',
        title: 'In toàn bộ tem',
        offAfterPrint
      })
    } catch (error) {
      handlePrintError(error)
    }
  })
}
