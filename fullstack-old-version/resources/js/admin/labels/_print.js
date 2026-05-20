'use strict'

import { handlePrintError, printElementById } from '../../common/print'

export const handleClickPrint = (offAfterPrint = true) => {
  $('#btn-print').off('click.label-print').on('click.label-print', async function (e) {
    e.preventDefault()

    try {
      await printElementById({
        elementId: 'to-print',
        title: 'In tem',
        offAfterPrint
      })
    } catch (error) {
      handlePrintError(error)
    }
  })
}
