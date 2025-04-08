document.addEventListener('DOMContentLoaded', () => {
  const ul = document.getElementById('DataObjectOneFieldUpdateUL')
  if (!ul) return

  const feedback = document.querySelector('.DataObjectOneFieldUpdateFeedback')
  const fieldNameInput = document.querySelector('#DataObjectOneFieldUpdateFieldName')
  const tableNameInput = document.querySelector('#DataObjectOneFieldUpdateTableName')

  const tableName = tableNameInput?.value || ''
  const fieldName = fieldNameInput?.value || ''
  const loadingText = 'updating data ...'

  const dirtyfixes = () => {
    const el = ul.querySelector('li span .updateField')
    if (!el) return

    const clone = el.cloneNode(true)
    clone.name = 'ApplyToAll'
    clone.id = 'ApplyToAll'

    if (el.nodeName.toLowerCase() !== 'select') {
      clone.type = el.type
      clone.value = el.value
    }

    document.getElementById('ApplyToAllButton')?.after(clone)
  }

  const retrieveDetailsFromInput = el => {
    const [table, id] = el.name.split('/')
    if (table !== tableName) {
      alert(`ERROR --- ${table} !== ${tableName}`)
      return {}
    }
    const value = el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value
    return { id, value }
  }

  const updateServer = (ids, value, elementSelector) => {
    if (feedback) {
      feedback.textContent = 'Running new update'
      feedback.classList.add('loading')
    }

    const url = `${DataObjectOneFieldUpdateURL}${tableName}/${fieldName}/?value=${encodeURIComponent(value)}&id=${ids.join()}`

    fetch(url)
      .then(res => res.text())
      .then(data => {
        if (feedback) {
          feedback.innerHTML = data
          feedback.classList.remove('loading')
        }
        const elements = typeof elementSelector === 'string'
          ? document.querySelectorAll(elementSelector)
          : [elementSelector]
        elements.forEach(el => {
          const holder = el.closest('li.fieldHolder')
          if (holder) {
            holder.classList.remove('loading', 'runningUpdate')
            holder.classList.add('updated')
          }
        })
      })
  }

  const setupChangeListener = () => {
    const inputs = ul.querySelectorAll('input.updateField, textarea.updateField, select.updateField')
    inputs.forEach(el => {
      el.closest('li.fieldHolder')?.classList.add('readyForAction')
      el.addEventListener('change', () => {
        if (el.dataset.ignoreInputChange === 'true') return

        const { id, value } = retrieveDetailsFromInput(el)
        if (!id) {
          feedback.textContent = 'ERROR: could not find record to update'
          return
        }
        if (!fieldName) {
          feedback.textContent = 'ERROR: could not find field to update'
          return
        }

        const holder = el.closest('li.fieldHolder')
        holder?.classList.add('runningUpdate', 'loading')
        if (feedback) feedback.textContent = loadingText

        updateServer([id], value, el)
      })
    })
  }

  const setupFilter = () => {
    const filter = document.getElementById('TextMatchFilter')
    if (!filter) return

    filter.addEventListener('input', () => {
      const terms = filter.value.toLowerCase().split(' ')
      ul.querySelectorAll('li label').forEach(label => {
        const labelText = label.textContent.toLowerCase()
        const match = terms.every(term => labelText.includes(term))
        label.closest('li').style.display = match ? '' : 'none'
      })
    })
  }

  const setupApplyAll = () => {
    const btn = document.getElementById('ApplyToAllButton')
    if (!btn) return

    btn.addEventListener('click', e => {
      e.preventDefault()
      if (!confirm('Are you sure you would like to apply the selected value to all visible elements?')) return

      const applyToAll = document.getElementById('ApplyToAll')
      if (!applyToAll) return

      const type = applyToAll.nodeName.toLowerCase()
      let value = applyToAll.value
      const ids = []

      let selector = ''
      if (type === 'select') {
        selector = '#DataObjectOneFieldUpdateUL li:not([style*="display: none"]) select'
      } else {
        const inputType = applyToAll.type.toLowerCase()
        if (inputType === 'checkbox') value = applyToAll.checked ? 1 : 0
        selector = '#DataObjectOneFieldUpdateUL li:not([style*="display: none"]) input'
      }

      document.querySelectorAll(selector).forEach(el => {
        el.value = value
        if (el.type === 'checkbox') el.checked = !!value

        const { id } = retrieveDetailsFromInput(el)
        ids.push(id)

        el.dataset.ignoreInputChange = 'true'
        el.dispatchEvent(new Event('change'))
        delete el.dataset.ignoreInputChange
      })

      updateServer(ids, value, selector)
    })
  }

  dirtyfixes()
  setupChangeListener()
  setupFilter()
  setupApplyAll()
})

