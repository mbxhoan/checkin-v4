'use strict'

import { handlePrintError, printElementById } from '../common/print'

export const print = async (offAfterPrint = true) => {
  try {
    await printElementById({
      elementId: 'to-print',
      title: 'In tem',
      offAfterPrint
    })
  } catch (error) {
    handlePrintError(error)
  }
}
