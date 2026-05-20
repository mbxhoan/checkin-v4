'use strict'

import { handlePrintError, printElementById } from '../common/print'

export const printOffline = async (elementId, offAfterPrint = true) => {
  try {
    await printElementById({
      elementId,
      title: 'In tem',
      offAfterPrint
    })
  } catch (error) {
    handlePrintError(error)
  }
}
