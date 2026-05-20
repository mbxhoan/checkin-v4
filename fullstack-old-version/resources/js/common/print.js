'use strict'

const DEFAULT_BOOTSTRAP_CSS_URL = 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css'

const defaultConfig = {
  enabled: false,
  provider: 'browser',
  fallback_to_browser: true,
  qz_printer: null
}

const getDirectPrintConfig = () => {
  const config = window.__DELFI_DIRECT_PRINT__
  if (!config || typeof config !== 'object') {
    return { ...defaultConfig }
  }

  return {
    ...defaultConfig,
    ...config
  }
}

const getInlinePrintStyles = () => {
  const styleElement = document.getElementById('style')
  return styleElement ? styleElement.innerHTML : ''
}

const getFontLinksHtml = () => {
  const fontLinkElement = document.getElementById('font-link')
  return fontLinkElement ? fontLinkElement.innerHTML : ''
}

const escapeHtml = (value) => {
  const source = String(value === null || value === undefined ? '' : value)
  return source
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

const buildPrintableDocument = (contentHtml, title = 'Print Page') => {
  const customStyle = getInlinePrintStyles()
  const fontLinks = getFontLinksHtml()

  return `
    <html>
      <head>
        <title>${escapeHtml(title)}</title>
        <meta charset="UTF-8">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        ${fontLinks}
        <link href="${DEFAULT_BOOTSTRAP_CSS_URL}" rel="stylesheet">
        <style>${customStyle}</style>
      </head>
      <body>${contentHtml}</body>
    </html>
  `
}

const notifyWarning = (message) => {
  if (window.toastr && typeof window.toastr.warning === 'function') {
    window.toastr.warning(message)
  } else {
    console.warn(message)
  }
}

const notifyError = (message) => {
  if (window.toastr && typeof window.toastr.error === 'function') {
    window.toastr.error(message)
  } else {
    console.error(message)
  }
}

const resolveQzPrinter = async (qz, preferredPrinter = null) => {
  if (preferredPrinter && String(preferredPrinter).trim() !== '') {
    return String(preferredPrinter).trim()
  }

  if (qz.printers && typeof qz.printers.getDefault === 'function') {
    return qz.printers.getDefault()
  }

  return null
}

const tryConfigureQzSecurity = (qz) => {
  if (!qz || !qz.security) {
    return
  }

  if (typeof qz.security.setCertificatePromise === 'function') {
    qz.security.setCertificatePromise((resolve) => resolve())
  }

  if (typeof qz.security.setSignaturePromise === 'function') {
    qz.security.setSignaturePromise(() => (resolve) => resolve())
  }
}

const printViaQz = async ({ htmlDocument, copies = 1, title = 'Print Page' }) => {
  if (!window.qz) {
    throw new Error('QZ Tray script chưa được nạp.')
  }

  const qz = window.qz
  tryConfigureQzSecurity(qz)

  if (!qz.websocket || typeof qz.websocket.connect !== 'function') {
    throw new Error('QZ Tray chưa sẵn sàng trên trình duyệt này.')
  }

  if (!qz.websocket.isActive()) {
    await qz.websocket.connect()
  }

  const config = getDirectPrintConfig()
  const printerName = await resolveQzPrinter(qz, config.qz_printer)
  if (!printerName) {
    throw new Error('Không tìm thấy máy in mặc định cho Direct Print.')
  }

  const qzConfig = qz.configs.create(printerName, {
    copies: Math.max(1, Number(copies || 1)),
    jobName: title
  })

  await qz.print(qzConfig, [
    {
      type: 'html',
      format: 'plain',
      data: htmlDocument
    }
  ])
}

const printViaBrowserIframe = ({ htmlDocument, title = 'Print Page', offAfterPrint = true }) =>
  new Promise((resolve, reject) => {
    const iframe = document.createElement('iframe')
    iframe.setAttribute('title', title)
    iframe.style.position = 'fixed'
    iframe.style.width = '0'
    iframe.style.height = '0'
    iframe.style.border = '0'
    iframe.style.opacity = '0'
    iframe.style.pointerEvents = 'none'
    iframe.style.bottom = '0'
    iframe.style.right = '0'
    document.body.appendChild(iframe)

    const iframeWindow = iframe.contentWindow
    if (!iframeWindow) {
      iframe.remove()
      reject(new Error('Không thể mở print frame trong trình duyệt.'))
      return
    }

    const cleanup = () => {
      setTimeout(() => {
        iframe.remove()
      }, 250)
    }

    let done = false

    const onPrinted = () => {
      if (done) {
        return
      }
      done = true
      cleanup()
      resolve()
    }

    if (offAfterPrint) {
      iframeWindow.onafterprint = onPrinted
    }

    const documentRef = iframeWindow.document
    documentRef.open()
    documentRef.write(htmlDocument)
    documentRef.close()

    setTimeout(() => {
      try {
        iframeWindow.focus()
        iframeWindow.print()
        if (!offAfterPrint) {
          onPrinted()
          return
        }
        setTimeout(onPrinted, 3000)
      } catch (error) {
        done = true
        cleanup()
        reject(error)
      }
    }, 250)
  })

const printHtml = async ({
  contentHtml,
  title = 'Print Page',
  copies = 1,
  offAfterPrint = true,
  forceBrowser = false
} = {}) => {
  if (!contentHtml || String(contentHtml).trim() === '') {
    throw new Error('Không có nội dung để in.')
  }

  const htmlDocument = buildPrintableDocument(contentHtml, title)
  const config = getDirectPrintConfig()
  const useDirectPrint = config.enabled && !forceBrowser

  if (useDirectPrint && config.provider === 'qz') {
    try {
      await printViaQz({
        htmlDocument,
        copies,
        title
      })
      return { mode: 'qz' }
    } catch (error) {
      if (!config.fallback_to_browser) {
        throw error
      }

      notifyWarning('Direct Print chưa sẵn sàng, hệ thống chuyển sang Browser Print.')
    }
  }

  await printViaBrowserIframe({
    htmlDocument,
    title,
    offAfterPrint
  })

  return { mode: 'browser' }
}

export const printElementById = async ({
  elementId,
  title = 'Print Page',
  copies = 1,
  offAfterPrint = true,
  forceBrowser = false
} = {}) => {
  const element = document.getElementById(elementId)
  if (!element) {
    throw new Error(`Không tìm thấy vùng in: #${elementId}`)
  }

  return printHtml({
    contentHtml: element.innerHTML,
    title,
    copies,
    offAfterPrint,
    forceBrowser
  })
}

export const printElementBySelector = async ({
  selector,
  title = 'Print Page',
  copies = 1,
  offAfterPrint = true,
  forceBrowser = false
} = {}) => {
  const element = document.querySelector(selector)
  if (!element) {
    throw new Error(`Không tìm thấy vùng in: ${selector}`)
  }

  return printHtml({
    contentHtml: element.innerHTML,
    title,
    copies,
    offAfterPrint,
    forceBrowser
  })
}

export const handlePrintError = (error, fallbackMessage = 'In thất bại. Vui lòng kiểm tra lại máy in.') => {
  const message = error instanceof Error ? error.message : fallbackMessage
  notifyError(message || fallbackMessage)
}
