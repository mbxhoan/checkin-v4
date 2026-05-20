'use strict'

import { handlePrintError, printElementById } from '../../common/print'

export const saveFormPrint = async (offAfterPrint = true, redirectTo = null) => {
  try {
    await printElementById({
      elementId: 'to-print',
      title: 'In tem',
      offAfterPrint
    })

    if (redirectTo) {
      window.location.href = redirectTo
    }
  } catch (error) {
    handlePrintError(error)
  }
}
