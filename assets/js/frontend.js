(function () {
  const data = window.CPL_Data || {};

  function isElementorEditorContext() {
    if (data.is_editor_context) {
      return true;
    }

    if (window.location.search.indexOf('elementor-preview=') !== -1) {
      return true;
    }

    if (
      window.elementorFrontend &&
      typeof window.elementorFrontend.isEditMode === 'function' &&
      window.elementorFrontend.isEditMode()
    ) {
      return true;
    }

    return false;
  }

  function updateEditorButtonState(button, isActive) {
    button.classList.toggle('is-active', isActive);
    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
  }

  function toggleEditorPreview(button) {
    const postId = button.getAttribute('data-post-id');
    const listType = button.getAttribute('data-list-type');
    const isActive = button.getAttribute('aria-pressed') !== 'true';

    if (!postId || !listType) {
      updateEditorButtonState(button, isActive);
      return;
    }

    document
      .querySelectorAll(
        '.cpl-toggle[data-post-id="' + String(postId) + '"][data-list-type="' + String(listType) + '"]'
      )
      .forEach((toggleButton) => {
        updateEditorButtonState(toggleButton, isActive);
      });
  }

  function initEditorPreviewMode() {
    if (window.CPL_EditorPreviewInitialized) {
      return;
    }

    window.CPL_EditorPreviewInitialized = true;

    document.addEventListener('click', function (event) {
      const button = event.target.closest('.cpl-toggle');

      if (!button || !document.documentElement.contains(button)) {
        return;
      }

      event.preventDefault();
      toggleEditorPreview(button);
    });

    window.CPL_InitButtons = function () {};
  }

  if (isElementorEditorContext()) {
    initEditorPreviewMode();
    return;
  }

  if (window.CPL_FrontendInitialized) {
    return;
  }

  window.CPL_FrontendInitialized = true;
  const visitorStorageKey = data.visitor_storage_key || 'cpl_session_key';
  const selectionStorageKey = data.selection_storage_key || 'cpl_selection_state';
  const maxSelections = Math.max(parseInt(data.max_selections, 10) || 8, 1);
  const maxCompare = Math.max(parseInt(data.max_compare, 10) || 4, 1);

  function getMaxSelections(listType) {
    return listType === 'compare' ? maxCompare : maxSelections;
  }
  const pendingSelections = new Set();
  const compareInstances = new WeakMap();
  let syncPromise = null;
  let refreshScheduled = false;
  let elementorHookRegistered = false;
  let ajaxHookRegistered = false;
  let toastHideTimer = null;
  let compareMarkupPromise = null;
  let favoriteMarkupPromise = null;
  let noticeOffsetRegistered = false;
  let compareResizeRegistered = false;
  let compareStickyRegistered = false;
  let compareCountAuthoritative = false;
  let favoriteCountAuthoritative = false;
  const mismatchReloadFlagPrefix = 'cpl_selection_page_reload';
  const comparePendingClassName = 'cpl-compare-pending';
  const favoritePendingClassName = 'cpl-favorite-grid-pending';
  const favoriteGridScopeSelectors = [
    '.elementor-loop-container',
    '.e-loop',
    '.elementor-posts-container',
    '.elementor-widget-loop-grid',
    '.elementor-widget-posts'
  ];

  function getContainer(scope) {
    return scope && typeof scope.querySelectorAll === 'function' ? scope : document;
  }

  function getSelectionKey(postId, listType) {
    return String(listType) + ':' + String(postId);
  }

  function getButtonsForSelection(postId, listType, scope) {
    const container = getContainer(scope);

    return container.querySelectorAll(
      '.cpl-toggle[data-post-id="' + String(postId) + '"][data-list-type="' + String(listType) + '"]'
    );
  }

  function isCompareRemoveButton(button) {
    return !!(
      button &&
      button.classList &&
      button.classList.contains('cpl-compare-shortcode__remove')
    );
  }

  function setButtonState(button, isActive) {
    if (!button) {
      return;
    }

    if (isCompareRemoveButton(button)) {
      button.classList.remove('is-active');
      button.setAttribute('aria-pressed', 'false');
      button.setAttribute('aria-checked', 'false');
      return;
    }

    button.classList.toggle('is-active', isActive);
    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
  }

  function normalizePathname(urlValue) {
    if (!urlValue) {
      return '';
    }

    try {
      return new URL(urlValue, window.location.origin).pathname.replace(/\/+$/, '') || '/';
    } catch (error) {
      return '';
    }
  }

  function getCurrentSelectionPageType() {
    const currentPath = normalizePathname(window.location.href);
    const comparePath = normalizePathname(data.compare_page_url);
    const favoritePath = normalizePathname(data.favorite_page_url);

    if (comparePath && currentPath === comparePath) {
      return 'compare';
    }

    if (favoritePath && currentPath === favoritePath) {
      return 'favorite';
    }

    return '';
  }

  function isCompareSelectionPage() {
    return getCurrentSelectionPageType() === 'compare';
  }

  function isFavoriteSelectionPage() {
    return getCurrentSelectionPageType() === 'favorite';
  }

  function isCompareCountElement(element) {
    return !!(
      element &&
      typeof element.getAttribute === 'function' &&
      element.getAttribute('data-cpl-selection-count') === 'compare'
    );
  }

  function isFavoriteCountElement(element) {
    return !!(
      element &&
      typeof element.getAttribute === 'function' &&
      element.getAttribute('data-cpl-selection-count') === 'favorite'
    );
  }

  function setCompareCountAuthoritative(isAuthoritative) {
    compareCountAuthoritative = !!isAuthoritative;
  }

  function setFavoriteCountAuthoritative(isAuthoritative) {
    favoriteCountAuthoritative = !!isAuthoritative;
  }

  function invalidateCompareCount(scope) {
    setCompareCountAuthoritative(false);

    getContainer(scope).querySelectorAll('[data-cpl-selection-count="compare"]').forEach((element) => {
      element.hidden = true;
      element.style.display = 'none';
      element.setAttribute('aria-hidden', 'true');
      element.setAttribute('data-cpl-authoritative-pending', '1');
      syncSelectionCountWidgetVisibility(element);
    });
  }

  function setComparePagePendingState(isPending) {
    document.documentElement.classList.toggle(comparePendingClassName, !!isPending);
  }

  function invalidateFavoriteCount(scope) {
    setFavoriteCountAuthoritative(false);

    getContainer(scope).querySelectorAll('[data-cpl-selection-count="favorite"]').forEach((element) => {
      element.hidden = true;
      element.style.display = 'none';
      element.setAttribute('aria-hidden', 'true');
      element.setAttribute('data-cpl-authoritative-pending', '1');
      syncSelectionCountWidgetVisibility(element);
    });
  }

  function setFavoritePagePendingState(isPending) {
    document.documentElement.classList.toggle(favoritePendingClassName, !!isPending);
  }

  function getFavoriteGridScopes(root) {
    const container = root && typeof root.querySelectorAll === 'function' ? root : document;
    const scopes = [];
    const seen = new Set();

    favoriteGridScopeSelectors.forEach((selector) => {
      container.querySelectorAll(selector).forEach((scope) => {
        if (!scope || seen.has(scope)) {
          return;
        }

        seen.add(scope);
        scopes.push(scope);
      });
    });

    return scopes;
  }

  function getCompareHost(scope) {
    return getContainer(scope).querySelector('[data-cpl-compare-host]');
  }

  function clearCompareHostMarkup(scope) {
    const compareHost = getCompareHost(scope || document);

    if (!compareHost) {
      return false;
    }

    if (isCompareSelectionPage()) {
      setComparePagePendingState(true);
    }

    compareHost.innerHTML = '';
    compareHost.setAttribute('data-cpl-compare-stale', '1');

    return true;
  }

  function replaceCompareHostMarkup(markup) {
    const compareHost = getCompareHost(document);

    if (!compareHost) {
      return false;
    }

    compareHost.innerHTML = typeof markup === 'string' ? markup : '';
    compareHost.removeAttribute('data-cpl-compare-stale');
    initCPLButtons(compareHost);
    syncNoticeOffset();
    setComparePagePendingState(false);

    return true;
  }

  function fetchCompareMarkup() {
    if (compareMarkupPromise) {
      return compareMarkupPromise;
    }

    if (!data.ajax_url || !data.nonce || !isCompareSelectionPage()) {
      return Promise.resolve(null);
    }

    setComparePagePendingState(true);
    clearCompareHostMarkup(document);

    const visitorKey = ensureVisitorKey();
    const formData = new FormData();
    formData.append('action', 'cpl_get_compare_markup');
    formData.append('nonce', data.nonce);

    if (visitorKey) {
      formData.append('visitor_key', visitorKey);
    }

    compareMarkupPromise = fetch(data.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then((response) => response.json())
      .then((response) => {
        if (!response || !response.success || !response.data) {
          return null;
        }

        if (response.data.session_key) {
          saveVisitorKey(response.data.session_key);
        }

        setCompareCountAuthoritative(true);
        setFavoriteCountAuthoritative(true);
        replaceStoredSelections(response.data);
        replaceCompareHostMarkup(response.data.markup || '');
        applyStateToButtons(document);

        return response.data;
      })
      .catch(() => {
        setComparePagePendingState(false);
        return null;
      })
      .finally(() => {
        compareMarkupPromise = null;
      });

    return compareMarkupPromise;
  }

  function normalizeSelectionIds(ids) {
    return Array.isArray(ids) ? Array.from(new Set(ids.map(String))) : [];
  }

  function countUniqueRenderedButtons(buttons) {
    const uniqueIds = new Set();

    buttons.forEach((button) => {
      const postId = button.getAttribute('data-post-id');

      if (postId) {
        uniqueIds.add(String(postId));
      }
    });

    return uniqueIds.size;
  }

  function getRenderedSelectionCount(listType) {
    if (listType === 'compare') {
      const compareContainer = document.querySelector('[data-cpl-compare]');

      if (!compareContainer) {
        return countUniqueRenderedButtons(
          document.querySelectorAll('.cpl-toggle[data-list-type="compare"][data-post-id]')
        );
      }

      return countUniqueRenderedButtons(
        compareContainer.querySelectorAll('.cpl-toggle[data-list-type="compare"][data-post-id]')
      );
    }

    if (listType === 'favorite') {
      let maxCount = 0;

      favoriteGridScopeSelectors.forEach((selector) => {
        document.querySelectorAll(selector).forEach((scope) => {
          const count = countUniqueRenderedButtons(
            scope.querySelectorAll('.cpl-toggle[data-list-type="favorite"][data-post-id]')
          );

          if (count > maxCount) {
            maxCount = count;
          }
        });
      });

      if (maxCount > 0) {
        return maxCount;
      }

      return countUniqueRenderedButtons(
        document.querySelectorAll('.cpl-toggle[data-list-type="favorite"][data-post-id]')
      );
    }

    return 0;
  }

  function getPrimaryFavoriteGridScope() {
    const markedHost = document.querySelector('[data-cpl-favorite-host="1"]');

    if (markedHost) {
      return markedHost;
    }

    let matchedScope = null;
    let matchedCount = 0;

    getFavoriteGridScopes(document).forEach((scope) => {
        const count = countUniqueRenderedButtons(
          scope.querySelectorAll('.cpl-toggle[data-list-type="favorite"][data-post-id]')
        );

        if (count > matchedCount) {
          matchedScope = scope;
          matchedCount = count;
        }
    });

    if (!matchedScope && isFavoriteSelectionPage()) {
      const fallbackScopes = getFavoriteGridScopes(document);
      matchedScope = fallbackScopes.length ? fallbackScopes[0] : null;
    }

    return matchedScope;
  }

  function getFavoriteGridScopeDescriptor(scope, root) {
    if (!scope || typeof scope.matches !== 'function') {
      return null;
    }

    const container = root && typeof root.querySelectorAll === 'function' ? root : document;

    for (let index = 0; index < favoriteGridScopeSelectors.length; index += 1) {
      const selector = favoriteGridScopeSelectors[index];

      if (!scope.matches(selector)) {
        continue;
      }

      const matches = Array.from(container.querySelectorAll(selector));
      const matchIndex = matches.indexOf(scope);

      if (matchIndex !== -1) {
        return { selector, index: matchIndex };
      }
    }

    return null;
  }

  function markFavoriteGridHost(scope) {
    if (!scope) {
      return null;
    }

    const descriptor = getFavoriteGridScopeDescriptor(scope, document);

    scope.setAttribute('data-cpl-favorite-host', '1');

    if (descriptor) {
      scope.setAttribute('data-cpl-favorite-selector', descriptor.selector);
      scope.setAttribute('data-cpl-favorite-selector-index', String(descriptor.index));
    }

    return scope;
  }

  function getFavoriteGridHost() {
    const existingHost = document.querySelector('[data-cpl-favorite-host="1"]');

    if (existingHost) {
      return existingHost;
    }

    return markFavoriteGridHost(getPrimaryFavoriteGridScope());
  }

  function setFavoriteGridVisibility(scope, isVisible) {
    if (!scope) {
      return;
    }

    if (isVisible) {
      scope.style.removeProperty('visibility');
      scope.style.removeProperty('opacity');
      scope.removeAttribute('aria-hidden');
      scope.removeAttribute('data-cpl-favorite-stale');
      setFavoritePagePendingState(false);
      return;
    }

    scope.style.setProperty('visibility', 'hidden');
    scope.style.setProperty('opacity', '0');
    scope.setAttribute('aria-hidden', 'true');
    scope.setAttribute('data-cpl-favorite-stale', '1');
    setFavoritePagePendingState(true);
  }

  function invalidateFavoriteGridScope(scope) {
    const host = markFavoriteGridHost(scope || getFavoriteGridHost());

    if (!host) {
      return null;
    }

    setFavoriteGridVisibility(host, false);
    host.innerHTML = '';

    return host;
  }

  function getFavoriteScopeIds(scope) {
    if (!scope) {
      return [];
    }

    return normalizeSelectionIds(
      Array.from(scope.querySelectorAll('.cpl-toggle[data-list-type="favorite"][data-post-id]')).map((button) => (
        button.getAttribute('data-post-id') || ''
      ))
    );
  }

  function resolveFavoriteGridScope(root, expectedIds, descriptor) {
    const container = root && typeof root.querySelectorAll === 'function' ? root : document;
    const normalizedExpectedIds = normalizeSelectionIds(expectedIds).sort();

    if (descriptor && descriptor.selector) {
      const descriptorMatches = Array.from(container.querySelectorAll(descriptor.selector));

      if (descriptorMatches[descriptor.index]) {
        return descriptorMatches[descriptor.index];
      }
    }

    const scopes = getFavoriteGridScopes(container);
    let bestScope = null;
    let bestScore = -1;

    scopes.forEach((scope) => {
      const scopeIds = getFavoriteScopeIds(scope).sort();
      let score = 0;

      if (scopeIds.length === normalizedExpectedIds.length) {
        score += 10;
      }

      normalizedExpectedIds.forEach((id) => {
        if (scopeIds.includes(id)) {
          score += 5;
        }
      });

      if (score > bestScore) {
        bestScope = scope;
        bestScore = score;
      }
    });

    if (bestScope) {
      return bestScope;
    }

    return scopes.length ? scopes[0] : null;
  }

  function replaceFavoriteGridMarkup(nextScope) {
    const host = getFavoriteGridHost();

    if (!host || !nextScope) {
      return false;
    }

    const replacement = document.importNode(nextScope, true);

    replacement.setAttribute('data-cpl-favorite-host', '1');

    if (host.hasAttribute('data-cpl-favorite-selector')) {
      replacement.setAttribute('data-cpl-favorite-selector', host.getAttribute('data-cpl-favorite-selector'));
    }

    if (host.hasAttribute('data-cpl-favorite-selector-index')) {
      replacement.setAttribute('data-cpl-favorite-selector-index', host.getAttribute('data-cpl-favorite-selector-index'));
    }

    host.replaceWith(replacement);
    setFavoriteGridVisibility(replacement, true);
    initCPLButtons(replacement);
    syncNoticeOffset();

    return true;
  }

  function fetchFavoriteMarkup(ids) {
    if (favoriteMarkupPromise) {
      return favoriteMarkupPromise;
    }

    if (!isFavoriteSelectionPage()) {
      return Promise.resolve(null);
    }

    const expectedIds = normalizeSelectionIds(ids);
    const host = invalidateFavoriteGridScope();

    if (!host) {
      setFavoritePagePendingState(false);
      return Promise.resolve(null);
    }

    const descriptor = {
      selector: host.getAttribute('data-cpl-favorite-selector') || '',
      index: parseInt(host.getAttribute('data-cpl-favorite-selector-index') || '0', 10) || 0
    };
    const requestUrl = new URL(window.location.href);

    requestUrl.searchParams.set('cpl_cache_bust', String(Date.now()));
    requestUrl.searchParams.set('cpl_favorite_refresh', '1');

    favoriteMarkupPromise = fetch(requestUrl.toString(), {
      method: 'GET',
      credentials: 'same-origin',
      cache: 'no-store'
    })
      .then((response) => response.text())
      .then((markup) => {
        const parser = new DOMParser();
        const parsedDocument = parser.parseFromString(markup, 'text/html');
        const nextScope = resolveFavoriteGridScope(parsedDocument, expectedIds, descriptor);

        if (nextScope) {
          replaceFavoriteGridMarkup(nextScope);
          return { favorite: expectedIds };
        }

        if (expectedIds.length < 1) {
          const currentHost = getFavoriteGridHost();

          if (currentHost) {
            setFavoriteGridVisibility(currentHost, true);
          } else {
            setFavoritePagePendingState(false);
          }
        }

        return { favorite: expectedIds };
      })
      .catch(() => {
        const currentHost = getFavoriteGridHost();

        if (currentHost) {
          setFavoriteGridVisibility(currentHost, true);
        } else {
          setFavoritePagePendingState(false);
        }

        return null;
      })
      .finally(() => {
        favoriteMarkupPromise = null;
      });

    return favoriteMarkupPromise;
  }

  function getRenderedFavoriteSelectionIds() {
    const scope = getPrimaryFavoriteGridScope();
    const buttons = scope
      ? scope.querySelectorAll('.cpl-toggle[data-list-type="favorite"][data-post-id]')
      : document.querySelectorAll('.cpl-toggle[data-list-type="favorite"][data-post-id]');

    return normalizeSelectionIds(
      Array.from(buttons).map((button) => button.getAttribute('data-post-id') || '')
    );
  }

  function favoriteSelectionIdsMatch(ids) {
    const expectedIds = normalizeSelectionIds(ids).sort();
    const renderedIds = getRenderedFavoriteSelectionIds().sort();

    if (expectedIds.length !== renderedIds.length) {
      return false;
    }

    return expectedIds.every((id, index) => id === renderedIds[index]);
  }

  function maybeReloadFavoritePageForMismatch(ids) {
    const expectedIds = normalizeSelectionIds(ids);

    if (favoriteSelectionIdsMatch(expectedIds)) {
      return;
    }

    if (!canUseStorage('sessionStorage')) {
      reloadCurrentPageWithCacheBust();
      return;
    }

    const reloadFlag = buildMismatchReloadFlag('favorite', expectedIds);

    if (window.sessionStorage.getItem(reloadFlag) === '1') {
      return;
    }

    window.sessionStorage.setItem(reloadFlag, '1');
    reloadCurrentPageWithCacheBust();
  }

  function getFavoriteItemElement(button, scope) {
    if (!button || typeof button.closest !== 'function') {
      return null;
    }

    const directMatch = button.closest('.e-loop-item, .elementor-grid-item, .elementor-post, article');

    if (directMatch && (!scope || scope.contains(directMatch))) {
      return directMatch;
    }

    let fallbackItem = null;
    let current = button.parentElement;

    while (current && current !== scope && current !== document.body) {
      const buttonCount = current.querySelectorAll('.cpl-toggle[data-list-type="favorite"][data-post-id]').length;

      if (buttonCount === 1) {
        fallbackItem = current;
      }

      current = current.parentElement;
    }

    return fallbackItem;
  }

  function reconcileFavoriteSelectionPage(ids) {
    if (!isFavoriteSelectionPage()) {
      return;
    }

    const expectedIds = new Set(normalizeSelectionIds(ids));
    const scope = getPrimaryFavoriteGridScope();

    if (!scope) {
      if (expectedIds.size < 1) {
        return;
      }

      maybeReloadFavoritePageForMismatch(Array.from(expectedIds));
      return;
    }

    scope.querySelectorAll('.cpl-toggle[data-list-type="favorite"][data-post-id]').forEach((button) => {
      const postId = String(button.getAttribute('data-post-id') || '');

      if (!postId || expectedIds.has(postId)) {
        return;
      }

      const item = getFavoriteItemElement(button, scope);

      if (item && item !== scope) {
        item.remove();
        return;
      }

      if (button.parentElement) {
        button.parentElement.remove();
      } else {
        button.remove();
      }
    });

    applyStateToButtons(scope);

    if (!favoriteSelectionIdsMatch(Array.from(expectedIds))) {
      maybeReloadFavoritePageForMismatch(Array.from(expectedIds));
    }
  }

  function buildMismatchReloadFlag(listType, ids) {
    const normalizedIds = Array.isArray(ids) ? Array.from(new Set(ids.map(String))).sort() : [];
    return mismatchReloadFlagPrefix + ':' + String(listType) + ':' + normalizedIds.join(',');
  }

  function reloadCurrentPageWithCacheBust() {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('cpl_cache_bust', String(Date.now()));
      window.location.replace(url.toString());
      return;
    } catch (error) {
      window.location.reload();
    }
  }

  function maybeReloadSelectionPageForMismatch(listType, ids) {
    const expectedCount = Array.isArray(ids) ? Array.from(new Set(ids.map(String))).length : 0;
    const renderedCount = getRenderedSelectionCount(listType);

    if (expectedCount === renderedCount) {
      return;
    }

    if (expectedCount < 1 && renderedCount < 1) {
      return;
    }

    if (!canUseStorage('sessionStorage')) {
      reloadCurrentPageWithCacheBust();
      return;
    }

    const reloadFlag = buildMismatchReloadFlag(listType, ids);

    if (window.sessionStorage.getItem(reloadFlag) === '1') {
      return;
    }

    window.sessionStorage.setItem(reloadFlag, '1');
    reloadCurrentPageWithCacheBust();
  }

  function canUseStorage(type) {
    try {
      const storage = window[type];
      const testKey = '__cpl_test__';
      storage.setItem(testKey, '1');
      storage.removeItem(testKey);
      return true;
    } catch (error) {
      return false;
    }
  }

  function isValidVisitorKey(value) {
    return typeof value === 'string' && /^(user_[1-9][0-9]*|guest_[a-f0-9-]{36})$/i.test(value);
  }

  function setCookie(name, value) {
    if (!name || !value) {
      return;
    }

    const parts = [
      name + '=' + encodeURIComponent(value),
      'path=/',
      'max-age=' + (30 * 24 * 60 * 60),
      'SameSite=Lax'
    ];

    if (window.location.protocol === 'https:') {
      parts.push('Secure');
    }

    document.cookie = parts.join('; ');
  }

  function getStoredVisitorKey() {
    if (!canUseStorage('localStorage')) {
      return '';
    }

    const value = window.localStorage.getItem(visitorStorageKey);
    return isValidVisitorKey(value) ? value : '';
  }

  function saveVisitorKey(value) {
    if (!isValidVisitorKey(value)) {
      return '';
    }

    if (canUseStorage('localStorage')) {
      window.localStorage.setItem(visitorStorageKey, value);
    }

    if (data.cookie_name) {
      setCookie(data.cookie_name, value);
    }

    return value;
  }

  function generateVisitorKey() {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
      return 'guest_' + window.crypto.randomUUID();
    }

    return '';
  }

  function ensureVisitorKey() {
    const storedKey = getStoredVisitorKey();

    if (storedKey) {
      return saveVisitorKey(storedKey);
    }

    const generatedKey = generateVisitorKey();

    if (generatedKey) {
      return saveVisitorKey(generatedKey);
    }

    return '';
  }

  function clampSelectionList(values, listType) {
    if (!Array.isArray(values)) {
      return [];
    }

    return Array.from(new Set(values.map(String))).slice(0, getMaxSelections(listType));
  }

  function getStoredSelections() {
    if (!canUseStorage('localStorage')) {
      return { favorite: [], compare: [] };
    }

    try {
      const parsed = JSON.parse(window.localStorage.getItem(selectionStorageKey) || '{}');
      return {
        favorite: clampSelectionList(parsed.favorite, 'favorite'),
        compare: clampSelectionList(parsed.compare, 'compare')
      };
    } catch (error) {
      return { favorite: [], compare: [] };
    }
  }

  function saveSelections(state) {
    if (!canUseStorage('localStorage')) {
      return;
    }

    window.localStorage.setItem(selectionStorageKey, JSON.stringify({
      favorite: clampSelectionList(state.favorite, 'favorite'),
      compare: clampSelectionList(state.compare, 'compare')
    }));
  }

  function updateSelectionCountElements(scope, stateOverride) {
    const container = getContainer(scope);
    const state = stateOverride || getStoredSelections();

    container.querySelectorAll('[data-cpl-selection-count]').forEach((element) => {
      const listType = element.getAttribute('data-cpl-selection-count');
      const isCompareCount = isCompareCountElement(element);
      const isFavoriteCount = isFavoriteCountElement(element);
      const count = Array.isArray(state[listType]) ? state[listType].length : 0;

      if (isCompareCount && !compareCountAuthoritative) {
        element.hidden = true;
        element.style.display = 'none';
        element.setAttribute('aria-hidden', 'true');
        element.setAttribute('data-cpl-authoritative-pending', '1');
        syncSelectionCountWidgetVisibility(element);
        return;
      }

      if (isFavoriteCount && !favoriteCountAuthoritative) {
        element.hidden = true;
        element.style.display = 'none';
        element.setAttribute('aria-hidden', 'true');
        element.setAttribute('data-cpl-authoritative-pending', '1');
        syncSelectionCountWidgetVisibility(element);
        return;
      }

      element.textContent = String(count);
      element.hidden = count < 1;
      element.style.display = count < 1 ? 'none' : '';
      element.removeAttribute('data-cpl-authoritative-pending');

      if (count < 1) {
        element.setAttribute('aria-hidden', 'true');
      } else {
        element.removeAttribute('aria-hidden');
      }

      syncSelectionCountWidgetVisibility(element);
    });

    syncSelectionDependentElements(container, state);
  }

  function getSelectionDependentClassName(listType) {
    if (listType === 'compare') {
      return 'hide-comp';
    }

    if (listType === 'favorite') {
      return 'hide-fav';
    }

    return '';
  }

  function syncSelectionDependentElements(scope, stateOverride) {
    const container = getContainer(scope);
    const state = stateOverride || getStoredSelections();

    ['compare', 'favorite'].forEach((listType) => {
      const className = getSelectionDependentClassName(listType);

      if (!className) {
        return;
      }

      const count = Array.isArray(state[listType]) ? state[listType].length : 0;
      const shouldHide = count < 1;

      container.querySelectorAll('.' + className).forEach((element) => {
        element.classList.toggle('cpl-selection-dependent--hidden', shouldHide);

        if (shouldHide) {
          element.setAttribute('aria-hidden', 'true');
        } else {
          element.removeAttribute('aria-hidden');
        }
      });
    });
  }

  function syncSelectionDependentElementsFromMarkup(scope) {
    syncSelectionDependentElements(scope, getStoredSelections());
  }

  function syncSelectionCountWidgetVisibility(element) {
    if (!element || typeof element.closest !== 'function') {
      return;
    }

    const widget = element.closest('.elementor-widget');

    if (!widget) {
      return;
    }

    const countElements = widget.querySelectorAll('[data-cpl-selection-count]');

    if (!countElements.length) {
      return;
    }

    const shouldHideWidget = !Array.from(countElements).some((countElement) => {
      const countValue = parseInt(countElement.textContent || '0', 10);
      return Number.isFinite(countValue) && countValue > 0;
    });

    widget.classList.toggle('cpl-selection-count-widget--hidden', shouldHideWidget);
    widget.hidden = shouldHideWidget;

    if (shouldHideWidget) {
      widget.setAttribute('aria-hidden', 'true');
    } else {
      widget.removeAttribute('aria-hidden');
    }
  }

  function syncSelectionCountWidgetVisibilityFromMarkup(scope) {
    const container = getContainer(scope);

    container.querySelectorAll('[data-cpl-selection-count]').forEach((element) => {
      syncSelectionCountWidgetVisibility(element);
    });
  }

  function getStoredSelectionCount(listType) {
    const state = getStoredSelections();
    return Array.isArray(state[listType]) ? state[listType].length : 0;
  }

  function isStoredSelectionActive(postId, listType) {
    const state = getStoredSelections();
    return Array.isArray(state[listType]) && state[listType].includes(String(postId));
  }

  function updateStoredSelection(postId, listType, isActive) {
    const state = getStoredSelections();
    const normalizedPostId = String(postId);
    const currentList = Array.isArray(state[listType]) ? state[listType] : [];
    const nextList = currentList.filter((value) => value !== normalizedPostId);

    if (isActive) {
      nextList.unshift(normalizedPostId);
    }

    state[listType] = clampSelectionList(nextList, listType);
    saveSelections(state);
    updateSelectionCountElements(document, state);
  }

  function replaceStoredSelections(payload) {
    const nextState = {
      favorite: clampSelectionList(payload.favorite, 'favorite'),
      compare: clampSelectionList(payload.compare, 'compare')
    };

    saveSelections(nextState);
    updateSelectionCountElements(document, nextState);
  }

  function applyStateToButtons(scope) {
    const container = getContainer(scope);
    const buttons = container.querySelectorAll('.cpl-toggle');
    const state = getStoredSelections();

    buttons.forEach((button) => {
      const postId = button.getAttribute('data-post-id');
      const listType = button.getAttribute('data-list-type');

      if (!postId || !listType || !Array.isArray(state[listType])) {
        return;
      }

      const isActive = state[listType].includes(String(postId));
      setButtonState(button, isActive);
    });

    updateSelectionCountElements(container, state);
  }

  function updateSelectionButtons(postId, listType, isActive, scope) {
    getButtonsForSelection(postId, listType, scope).forEach((button) => {
      setButtonState(button, isActive);
    });
  }

  function markSelectionPending(postId, listType, isPending, scope) {
    const selectionKey = getSelectionKey(postId, listType);

    if (isPending) {
      pendingSelections.add(selectionKey);
    } else {
      pendingSelections.delete(selectionKey);
    }

    getButtonsForSelection(postId, listType, scope).forEach((button) => {
      if (isPending) {
        button.dataset.cplPending = '1';
        button.setAttribute('aria-busy', 'true');
      } else {
        delete button.dataset.cplPending;
        button.removeAttribute('aria-busy');
      }
    });
  }

  function isSelectionPending(postId, listType) {
    return pendingSelections.has(getSelectionKey(postId, listType));
  }

  function shouldReloadAfterRemoval(listType, isActive) {
    return !isActive && data.reload_on_remove === String(listType || '');
  }

  function getLoadingOverlayScope(button, listType) {
    if (!button || typeof button.closest !== 'function') {
      return null;
    }

    if (listType === 'compare') {
      return (
        button.closest('.cpl-compare-shortcode__desktop-shell') ||
        button.closest('.cpl-compare-shortcode__mobile-wrap') ||
        button.closest('[data-cpl-compare]') ||
        button.closest('.cpl-compare-shortcode')
      );
    }

    const scopeSelectors = [
      '.elementor-loop-container',
      '.e-loop',
      '.elementor-posts-container',
      '.elementor-widget-loop-grid',
      '.elementor-widget-posts'
    ];

    for (let index = 0; index < scopeSelectors.length; index += 1) {
      const scope = button.closest(scopeSelectors[index]);

      if (!scope) {
        continue;
      }

      const matchingButtons = scope.querySelectorAll('.cpl-toggle[data-list-type="' + String(listType) + '"]');

      if (matchingButtons.length > 0) {
        return scope;
      }
    }

    const fallbackWidget = button.closest('.elementor-widget');

    if (fallbackWidget) {
      return fallbackWidget;
    }

    return button.parentElement || null;
  }

  function findDirectLoadingOverlay(scope) {
    if (!scope || !scope.children) {
      return null;
    }

    return Array.from(scope.children).find((child) => child.classList && child.classList.contains('cpl-loading-overlay')) || null;
  }

  function getLoadingOverlayElements(scope, listType) {
    if (!scope) {
      return null;
    }

    let overlay = findDirectLoadingOverlay(scope);

    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'cpl-loading-overlay';
      overlay.setAttribute('aria-hidden', 'true');

      const panel = document.createElement('div');
      panel.className = 'cpl-loading-overlay__panel';
      panel.setAttribute('role', 'status');
      panel.setAttribute('aria-live', 'polite');

      const spinner = document.createElement('span');
      spinner.className = 'cpl-loading-overlay__spinner';
      spinner.setAttribute('aria-hidden', 'true');

      const text = document.createElement('span');
      text.className = 'cpl-loading-overlay__text';
      text.textContent = 'Cargando...';

      panel.appendChild(spinner);
      panel.appendChild(text);
      overlay.appendChild(panel);
    }

    scope.classList.add('cpl-loading-scope');
    scope.classList.toggle('cpl-loading-scope--compare', listType === 'compare');

    if (overlay.parentNode !== scope) {
      scope.appendChild(overlay);
    }

    overlay.classList.toggle('cpl-loading-overlay--compare', listType === 'compare');

    return {
      overlay: overlay,
      text: overlay.querySelector('.cpl-loading-overlay__text')
    };
  }

  function showLoadingOverlay(scope, message, listType) {
    const overlayElements = getLoadingOverlayElements(scope, listType);

    if (!overlayElements) {
      return;
    }

    if (overlayElements.text && message) {
      overlayElements.text.textContent = message;
    }

    overlayElements.overlay.classList.add('is-visible');
    overlayElements.overlay.setAttribute('aria-hidden', 'false');
  }

  function hideLoadingOverlay(scope) {
    if (!scope) {
      return;
    }

    const overlay = findDirectLoadingOverlay(scope);

    if (!overlay) {
      return;
    }

    overlay.classList.remove('is-visible');
    overlay.setAttribute('aria-hidden', 'true');
  }

  function getNoticeElements() {
    let noticeRegion = document.querySelector('.cpl-notice-region');

    if (!noticeRegion) {
      noticeRegion = document.createElement('div');
      noticeRegion.className = 'cpl-notice-region';
      noticeRegion.setAttribute('aria-live', 'polite');
      noticeRegion.setAttribute('aria-atomic', 'true');

      const notice = document.createElement('div');
      notice.className = 'cpl-notice';

      const message = document.createElement('div');
      message.className = 'cpl-notice__message';

      const actions = document.createElement('div');
      actions.className = 'cpl-notice__actions';

      const actionLink = document.createElement('a');
      actionLink.className = 'elementor-button cpl-notice__action';
      actionLink.hidden = true;
      actions.appendChild(actionLink);

      notice.appendChild(message);
      notice.appendChild(actions);
      noticeRegion.appendChild(notice);
    }

    mountNoticeRegion(noticeRegion);

    return {
      noticeRegion: noticeRegion,
      notice: noticeRegion.querySelector('.cpl-notice'),
      message: noticeRegion.querySelector('.cpl-notice__message'),
      actions: noticeRegion.querySelector('.cpl-notice__actions'),
      actionLink: noticeRegion.querySelector('.cpl-notice__action')
    };
  }

  function getToastMessage(listType, action, isActive) {
    const normalizedAction = action === 'added' || action === 'removed'
      ? action
      : (isActive ? 'added' : 'removed');

    if (listType === 'favorite') {
      return normalizedAction === 'added'
        ? 'Vehículo agregado a favoritos'
        : 'Vehículo eliminado de favoritos';
    }

    if (listType === 'compare') {
      return normalizedAction === 'added'
        ? 'Vehículo agregado a comparar'
        : 'Vehículo eliminado de comparar';
    }

    return '';
  }

  function getSelectionLimitMessage(listType) {
    if (listType === 'favorite') {
      return 'Solo puedes agregar hasta ' + String(maxSelections) + ' vehículos a favoritos';
    }

    if (listType === 'compare') {
      return 'Solo puedes agregar hasta ' + String(maxCompare) + ' vehículos a comparar';
    }

    return 'Solo puedes agregar hasta ' + String(maxSelections) + ' vehículos';
  }

  function getNoticeTone(action, isActive) {
    if (action === 'error') {
      return 'is-error';
    }

    const normalizedAction = action === 'added' || action === 'removed'
      ? action
      : (isActive ? 'added' : 'removed');

    return normalizedAction === 'removed' ? 'is-removed' : 'is-added';
  }

  function getNoticeActionConfig(listType, action, isActive) {
    const normalizedAction = action === 'added' || action === 'removed'
      ? action
      : (isActive ? 'added' : 'removed');

    if (normalizedAction !== 'added') {
      return null;
    }

    if (listType === 'favorite' && data.favorite_page_url) {
      return {
        url: data.favorite_page_url,
        label: 'Ver favoritos'
      };
    }

    if (listType === 'compare' && data.compare_page_url) {
      return {
        url: data.compare_page_url,
        label: 'Ver comparar'
      };
    }

    return null;
  }

  function getNoticeHeaderCandidates() {
    const headerSelectors = [
      '.auto-head',
      '.elementor-location-header',
      '[data-elementor-type="header"]',
      '.site-header',
      'header'
    ];

    const headers = [];
    const seen = new Set();

    headerSelectors.forEach((selector) => {
      document.querySelectorAll(selector).forEach((header) => {
        if (seen.has(header)) {
          return;
        }

        seen.add(header);
        headers.push(header);
      });
    });

    return headers;
  }

  function isNoticeHeaderVisible(header) {
    if (!header || typeof window.getComputedStyle !== 'function') {
      return false;
    }

    const rect = header.getBoundingClientRect();
    const computedStyle = window.getComputedStyle(header);
    const visibleHeight = Math.max(0, Math.min(rect.bottom, window.innerHeight) - Math.max(rect.top, 0));
    const minimumVisibleHeight = Math.min(Math.max(Math.round(rect.height * 0.25), 8), Math.round(rect.height || 0));

    if (
      computedStyle.display === 'none' ||
      computedStyle.visibility === 'hidden' ||
      parseFloat(computedStyle.opacity || '1') === 0
    ) {
      return false;
    }

    if (rect.width <= 0 || rect.height <= 0) {
      return false;
    }

    if (header.classList.contains('auto-head') && header.classList.contains('is-hidden')) {
      return false;
    }

    return visibleHeight >= Math.max(minimumVisibleHeight, 1);
  }

  function getPrimaryAutoHideHeader() {
    return document.querySelector('.auto-head') || null;
  }

  function mountNoticeRegion(noticeRegion) {
    if (!noticeRegion) {
      return;
    }

    if (noticeRegion.parentNode !== document.body) {
      document.body.appendChild(noticeRegion);
    }
  }

  function getAdminBarOffset() {
    const adminBar = document.getElementById('wpadminbar');

    if (!adminBar || typeof window.getComputedStyle !== 'function') {
      return 0;
    }

    const computedStyle = window.getComputedStyle(adminBar);
    const rect = adminBar.getBoundingClientRect();

    if (computedStyle.display === 'none' || computedStyle.visibility === 'hidden') {
      return 0;
    }

    return Math.max(Math.ceil(rect.bottom), 0);
  }

  function getNoticeHeaderOffset() {
    const headers = getNoticeHeaderCandidates();
    const adminBarOffset = getAdminBarOffset();
    let stickyBottom = 0;
    const autoHideHeader = getPrimaryAutoHideHeader();

    if (!headers.length || typeof window.getComputedStyle !== 'function') {
      return adminBarOffset;
    }

    if (autoHideHeader && isNoticeHeaderVisible(autoHideHeader)) {
      const rect = autoHideHeader.getBoundingClientRect();
      return Math.max(Math.ceil(rect.bottom), adminBarOffset);
    }

    headers.forEach((header) => {
      if (autoHideHeader && header === autoHideHeader) {
        return;
      }

      if (!isNoticeHeaderVisible(header)) {
        return;
      }

      const rect = header.getBoundingClientRect();
      const computedStyle = window.getComputedStyle(header);
      const headerBottom = Math.max(rect.bottom, 0);

      if (computedStyle.position === 'fixed' || computedStyle.position === 'sticky') {
        stickyBottom = Math.max(stickyBottom, headerBottom);
      }
    });

    if (stickyBottom > 0) {
      return Math.max(Math.ceil(stickyBottom), adminBarOffset);
    }

    return adminBarOffset;
  }

  function syncNoticeOffset() {
    const noticeRegion = document.querySelector('.cpl-notice-region');

    if (noticeRegion) {
      mountNoticeRegion(noticeRegion);
      noticeRegion.style.setProperty('--cpl-notice-offset', getNoticeHeaderOffset() + 'px');
    }
  }

  function registerNoticeOffsetHandlers() {
    if (noticeOffsetRegistered) {
      return;
    }

    noticeOffsetRegistered = true;
    window.addEventListener('resize', syncNoticeOffset);
    window.addEventListener('scroll', syncNoticeOffset, { passive: true });
  }

  function showToast(message, toneClass, actionConfig) {
    if (!message) {
      return;
    }

    const noticeElements = getNoticeElements();
    const noticeRegion = noticeElements.noticeRegion;
    const notice = noticeElements.notice;
    const messageNode = noticeElements.message;
    const actionsNode = noticeElements.actions;
    const actionLink = noticeElements.actionLink;

    messageNode.textContent = message;
    notice.classList.remove('is-added', 'is-removed', 'is-error');

    if (toneClass) {
      notice.classList.add(toneClass);
    }

    if (actionConfig && actionConfig.url) {
      actionLink.href = actionConfig.url;
      actionLink.textContent = actionConfig.label;
      actionLink.hidden = false;
      actionsNode.hidden = false;
    } else {
      actionLink.hidden = true;
      actionLink.removeAttribute('href');
      actionLink.textContent = '';
      actionsNode.hidden = true;
    }

    registerNoticeOffsetHandlers();
    syncNoticeOffset();

    noticeRegion.classList.remove('is-visible');
    notice.classList.remove('is-visible');
    void noticeRegion.offsetWidth;
    noticeRegion.classList.add('is-visible');
    notice.classList.add('is-visible');

    if (toastHideTimer) {
      window.clearTimeout(toastHideTimer);
    }

    toastHideTimer = window.setTimeout(function () {
      noticeRegion.classList.remove('is-visible');
      notice.classList.remove('is-visible');
    }, actionConfig && actionConfig.url ? 5200 : 3200);
  }

  function syncSelectionsFromServer() {
    if (syncPromise) {
      return syncPromise;
    }

    if (!data.ajax_url || !data.nonce) {
      syncPromise = Promise.resolve();
      return syncPromise;
    }

    const visitorKey = ensureVisitorKey();
    const formData = new FormData();
    formData.append('action', 'cpl_get_selections');
    formData.append('nonce', data.nonce);

    if (visitorKey) {
      formData.append('visitor_key', visitorKey);
    }

    syncPromise = fetch(data.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then((response) => response.json())
      .then((response) => {
        if (!response || !response.success || !response.data) {
          return;
        }

        if (response.data.session_key) {
          saveVisitorKey(response.data.session_key);
        }

        setCompareCountAuthoritative(true);
        setFavoriteCountAuthoritative(true);
        replaceStoredSelections(response.data);
        applyStateToButtons(document);

        const currentSelectionPageType = getCurrentSelectionPageType();

        if (currentSelectionPageType === 'compare') {
          return fetchCompareMarkup();
        }

        if (currentSelectionPageType === 'favorite') {
          return fetchFavoriteMarkup(response.data.favorite || []).then(function () {
            reconcileFavoriteSelectionPage(response.data.favorite || []);
          });
        }

        if (currentSelectionPageType && Array.isArray(response.data[currentSelectionPageType])) {
          maybeReloadSelectionPageForMismatch(currentSelectionPageType, response.data[currentSelectionPageType]);
        }
      })
      .catch(() => {})
      .finally(() => {
        syncPromise = null;
      });

    return syncPromise;
  }

  function handleToggleError(response, listType) {
    const message = response && response.data && response.data.message
      ? response.data.message
      : getSelectionLimitMessage(listType);

    showToast(message, getNoticeTone('error', false), null);
  }

  function clearCompare(button) {
    if (isElementorEditorContext()) {
      return;
    }

    if (!button || !data.ajax_url || !data.nonce || button.dataset.cplPending === '1') {
      return;
    }

    const visitorKey = ensureVisitorKey();
    const loadingOverlayScope = getLoadingOverlayScope(button, 'compare');
    let keepLoadingOverlayVisible = false;

    button.dataset.cplPending = '1';
    button.setAttribute('aria-busy', 'true');
    showLoadingOverlay(loadingOverlayScope, 'Cargando...', 'compare');

    const formData = new FormData();
    formData.append('action', 'cpl_clear_compare');
    formData.append('nonce', data.nonce);

    if (visitorKey) {
      formData.append('visitor_key', visitorKey);
    }

    fetch(data.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then((response) => response.json())
      .then((response) => {
        if (!response || !response.success || !response.data) {
          showToast('No se pudo limpiar el comparador.', getNoticeTone('error', false), null);
          return;
        }

        if (response.data.session_key) {
          saveVisitorKey(response.data.session_key);
        }

        replaceStoredSelections(response.data);
        applyStateToButtons(document);
        keepLoadingOverlayVisible = true;
        fetchCompareMarkup().finally(function () {
          hideLoadingOverlay(loadingOverlayScope);
        });
      })
      .catch(() => {
        showToast('No se pudo limpiar el comparador.', getNoticeTone('error', false), null);
      })
      .finally(() => {
        delete button.dataset.cplPending;
        button.removeAttribute('aria-busy');

        if (!keepLoadingOverlayVisible) {
          hideLoadingOverlay(loadingOverlayScope);
        }
      });
  }

  function toggleSelection(button) {
    if (isElementorEditorContext()) {
      return;
    }

    const postId = button.getAttribute('data-post-id');
    const listType = button.getAttribute('data-list-type');
    const visitorKey = ensureVisitorKey();
    const isCurrentlyActive = isCompareRemoveButton(button)
      ? isStoredSelectionActive(postId, listType)
      : button.getAttribute('aria-pressed') === 'true';
    const shouldShowRemovalLoading = isCurrentlyActive && shouldReloadAfterRemoval(listType, false);
    const loadingOverlayScope = shouldShowRemovalLoading ? getLoadingOverlayScope(button, listType) : null;
    let keepLoadingOverlayVisible = false;

    if (!postId || !listType || !data.ajax_url || !data.nonce || isSelectionPending(postId, listType)) {
      return;
    }

    if (!isCurrentlyActive && getStoredSelectionCount(listType) >= getMaxSelections(listType)) {
      showToast(getSelectionLimitMessage(listType), getNoticeTone('error', false), null);
      return;
    }

    if (shouldShowRemovalLoading) {
      showLoadingOverlay(loadingOverlayScope, 'Cargando...', listType);
    }

    markSelectionPending(postId, listType, true);

    const formData = new FormData();
    formData.append('action', 'cpl_toggle_selection');
    formData.append('nonce', data.nonce);
    formData.append('post_id', postId);
    formData.append('list_type', listType);

    if (visitorKey) {
      formData.append('visitor_key', visitorKey);
    }

    fetch(data.ajax_url, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
      .then((response) => response.json())
      .then((response) => {
        if (!response || !response.success || !response.data) {
          handleToggleError(response, listType);
          return;
        }

        const responsePostId = response.data.post_id || postId;
        const responseListType = response.data.list_type || listType;
        const responseAction = response.data.action || '';
        const isActive = !!response.data.is_active;

        if (response.data.session_key) {
          saveVisitorKey(response.data.session_key);
        }

        updateStoredSelection(responsePostId, responseListType, isActive);
        updateSelectionButtons(responsePostId, responseListType, isActive);
        showToast(
          getToastMessage(responseListType, responseAction, isActive),
          getNoticeTone(responseAction, isActive),
          getNoticeActionConfig(responseListType, responseAction, isActive)
        );

        if (shouldReloadAfterRemoval(responseListType, isActive)) {
          keepLoadingOverlayVisible = true;

          if (responseListType === 'favorite') {
            invalidateFavoriteGridScope();
            syncSelectionsFromServer().finally(function () {
              hideLoadingOverlay(loadingOverlayScope);
            });
          } else {
            fetchCompareMarkup().finally(function () {
              hideLoadingOverlay(loadingOverlayScope);
            });
          }
        }
      })
      .catch(() => {
        showToast('No se pudo actualizar la selección.', getNoticeTone('error', false), null);
      })
      .finally(() => {
        markSelectionPending(postId, listType, false);

        if (shouldShowRemovalLoading && !keepLoadingOverlayVisible) {
          hideLoadingOverlay(loadingOverlayScope);
        }
      });
  }

  function handleDocumentClick(event) {
    const compareToggle = event.target.closest('[data-cpl-compare-toggle]');

    if (compareToggle && document.documentElement.contains(compareToggle)) {
      const instance = compareToggle.closest('[data-cpl-compare]');

      if (instance) {
        event.preventDefault();
        toggleCompareAdditionalRows(instance);
        return;
      }
    }

    const clearButton = event.target.closest('.cpl-compare-shortcode__clear');

    if (clearButton && document.documentElement.contains(clearButton)) {
      event.preventDefault();
      clearCompare(clearButton);
      return;
    }

    const button = event.target.closest('.cpl-toggle');

    if (!button || !document.documentElement.contains(button)) {
      return;
    }

    event.preventDefault();
    toggleSelection(button);
  }

  function extractAjaxAction(payload) {
    if (!payload) {
      return '';
    }

    if (typeof payload === 'string') {
      return new URLSearchParams(payload).get('action') || '';
    }

    if (typeof FormData !== 'undefined' && payload instanceof FormData) {
      return payload.get('action') || '';
    }

    if (typeof payload === 'object' && typeof payload.action === 'string') {
      return payload.action;
    }

    return '';
  }

  function getAjaxAction(settings) {
    const action = extractAjaxAction(settings && settings.data);

    if (action) {
      return action;
    }

    if (!settings || typeof settings.url !== 'string') {
      return '';
    }

    try {
      return new URL(settings.url, window.location.origin).searchParams.get('action') || '';
    } catch (error) {
      return '';
    }
  }

  function isBPFWEAjaxRequest(settings) {
    const action = getAjaxAction(settings);

    return action === 'post_filter_results' || action === 'load_posts_ajax';
  }

  function getCompareVisibleColumns(totalColumns) {
    if (window.innerWidth < 768) {
      return 1;
    }

    if (window.innerWidth < 1024) {
      return 2;
    }

    return 4;
  }

  function getCompareMinColumnWidth() {
    if (window.innerWidth < 768) {
      return 220;
    }

    if (window.innerWidth < 1024) {
      return 210;
    }

    return 200;
  }

  function isCompareMobileViewport() {
    return window.innerWidth < 768;
  }

  function getCompareScrollElement(instance) {
    if (!instance) {
      return null;
    }

    if (isCompareMobileViewport()) {
      return (
        instance.querySelector('.cpl-compare-shortcode__mobile-scroll') ||
        instance.querySelector('.cpl-compare-shortcode__scroll')
      );
    }

    return instance.querySelector('.cpl-compare-shortcode__scroll');
  }

  function getCompareHeaderElement(instance) {
    if (!instance) {
      return null;
    }

    if (isCompareMobileViewport()) {
      return instance.querySelector('.cpl-compare-shortcode__mobile-card .cpl-compare-shortcode__vehicle-card');
    }

    return instance.querySelector('.cpl-compare-shortcode__header');
  }

  function getCompareHeaderTrackElement(instance) {
    if (!instance || isCompareMobileViewport()) {
      return null;
    }

    return instance.querySelector('.cpl-compare-shortcode__header-track');
  }

  function syncCompareHeaderScroll(instance, scrollLeft) {
    const headerTrack = getCompareHeaderTrackElement(instance);

    if (!headerTrack) {
      return;
    }

    headerTrack.style.transform = 'translateX(' + (Math.max(scrollLeft || 0, 0) * -1) + 'px)';
  }

  function getCompareDesktopShellElement(instance) {
    if (!instance || isCompareMobileViewport()) {
      return null;
    }

    return instance.querySelector('.cpl-compare-shortcode__desktop-shell');
  }

  function getCompareHeaderWrapElement(instance) {
    if (!instance || isCompareMobileViewport()) {
      return null;
    }

    return instance.querySelector('.cpl-compare-shortcode__header-wrap');
  }

  function getCompareHeaderPlaceholderElement(instance) {
    if (!instance || isCompareMobileViewport()) {
      return null;
    }

    return instance.querySelector('.cpl-compare-shortcode__header-placeholder');
  }

  function getCompareToolbarElement(instance) {
    if (!instance || isCompareMobileViewport()) {
      return null;
    }

    return instance.querySelector('.cpl-compare-shortcode__toolbar');
  }

  function getCompareStickyTopOffset() {
    const candidates = document.querySelectorAll(
      'header, [role="banner"], .site-header, .elementor-location-header, .elementor-sticky--active, .elementor-sticky--effects'
    );
    let maxBottom = 0;

    candidates.forEach((candidate) => {
      if (!candidate || candidate.closest('[data-cpl-compare]')) {
        return;
      }

      const style = window.getComputedStyle(candidate);
      const isElementorSticky =
        candidate.classList.contains('elementor-sticky--active') ||
        candidate.classList.contains('elementor-sticky--effects');
      const isHeaderLike =
        candidate.matches('header, [role="banner"], .site-header, .elementor-location-header') ||
        !!candidate.closest('.elementor-location-header');

      if (!isHeaderLike && !isElementorSticky) {
        return;
      }

      if (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity || '1') === 0) {
        return;
      }

      if (style.position !== 'fixed' && style.position !== 'sticky' && !isElementorSticky) {
        return;
      }

      const rect = candidate.getBoundingClientRect();

      if (rect.height <= 0 || rect.bottom <= 0 || rect.top > 40) {
        return;
      }

      maxBottom = Math.max(maxBottom, rect.bottom);
    });

    return Math.max(0, Math.ceil(maxBottom));
  }

  function resetCompareDetachedHeader(instance) {
    const headerWrap = getCompareHeaderWrapElement(instance);
    const placeholder = getCompareHeaderPlaceholderElement(instance);
    const toolbar = getCompareToolbarElement(instance);

    if (headerWrap) {
      headerWrap.classList.remove('is-stuck');
    }

    if (placeholder) {
      placeholder.style.height = '0px';
    }

    if (toolbar) {
      toolbar.classList.remove('is-stuck');
    }
  }

  function updateCompareStickyState(instance) {
    if (!instance || isCompareMobileViewport()) {
      resetCompareDetachedHeader(instance);
      return;
    }

    const shell = getCompareDesktopShellElement(instance);
    const headerWrap = getCompareHeaderWrapElement(instance);
    const headerElement = getCompareHeaderElement(instance);
    const placeholder = getCompareHeaderPlaceholderElement(instance);
    const toolbar = getCompareToolbarElement(instance);
    if (!shell || !headerWrap || !headerElement || !placeholder) {
      return;
    }

    const stickyTop = getCompareStickyTopOffset();
    const shellRect = shell.getBoundingClientRect();
    const headerHeight = Math.ceil(headerElement.getBoundingClientRect().height || headerWrap.getBoundingClientRect().height || 0);
    const shellWidth = Math.ceil(shellRect.width);
    const shellLeft = Math.round(shellRect.left);
    const shellAllowsFixed = shellRect.bottom > stickyTop + headerHeight;
    const shouldStick = shellRect.top <= stickyTop && shellAllowsFixed;

    instance.style.setProperty('--cpl-compare-sticky-top', stickyTop + 'px');
    instance.style.setProperty('--cpl-compare-sticky-left', shellLeft + 'px');
    instance.style.setProperty('--cpl-compare-sticky-width', shellWidth + 'px');

    if (toolbar) {
      toolbar.style.height = headerHeight + 'px';
    }

    if (shouldStick) {
      placeholder.style.height = headerHeight + 'px';
      headerWrap.classList.add('is-stuck');

      if (toolbar) {
        toolbar.classList.add('is-stuck');
      }

      return;
    }

    resetCompareDetachedHeader(instance);
  }

  function getCompareState(instance) {
    if (!compareInstances.has(instance)) {
      compareInstances.set(instance, {
        index: 0,
        visibleColumns: 1,
        columnWidth: 0,
        totalColumns: parseInt(instance.getAttribute('data-total-columns'), 10) || 0
      });
    }

    return compareInstances.get(instance);
  }

  function toggleCompareAdditionalRows(instance) {
    const isExpanded = instance.getAttribute('data-cpl-compare-expanded') === '1';
    const nextExpanded = !isExpanded;

    instance.setAttribute('data-cpl-compare-expanded', nextExpanded ? '1' : '0');

    instance.querySelectorAll('[data-cpl-compare-additional]').forEach((row) => {
      row.hidden = !nextExpanded;
    });

    instance.querySelectorAll('[data-cpl-compare-expand-row]').forEach((row) => {
      row.hidden = nextExpanded;
    });

    instance.querySelectorAll('[data-cpl-compare-collapse-row]').forEach((row) => {
      row.hidden = !nextExpanded;
    });

    instance.querySelectorAll('[data-cpl-compare-toggle]').forEach((button) => {
      button.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
    });

    updateCompareLayout(instance, false);
    updateCompareStickyState(instance);
  }

  function syncCompareBodyFillers(instance, visibleColumns, totalColumns) {
    if (!instance || isCompareMobileViewport()) {
      return;
    }

    const rows = instance.querySelectorAll('.cpl-compare-shortcode__table--body > tbody > tr:not(.cpl-compare-shortcode__expand-row):not(.cpl-compare-shortcode__collapse-row):not(.cpl-compare-shortcode__group-row)');

    if (!rows.length) {
      return;
    }

    const missingColumns = Math.max((visibleColumns || 0) - (totalColumns || 0), 0);

    rows.forEach((row) => {
      row.querySelectorAll('.cpl-compare-shortcode__value--filler').forEach((cell) => {
        cell.remove();
      });

      for (let index = 0; index < missingColumns; index += 1) {
        const fillerCell = document.createElement('td');
        fillerCell.className = 'cpl-compare-shortcode__value cpl-compare-shortcode__value--filler';
        fillerCell.setAttribute('aria-hidden', 'true');
        row.appendChild(fillerCell);
      }
    });
  }

  function updateCompareButtons(instance, state) {
    const prevButton = instance.querySelector('.cpl-compare-shortcode__nav--prev');
    const nextButton = instance.querySelector('.cpl-compare-shortcode__nav--next');
    const maxIndex = Math.max(0, state.totalColumns - state.visibleColumns);
    const shouldShow = state.totalColumns > state.visibleColumns;

    if (prevButton) {
      prevButton.disabled = state.index <= 0;
      prevButton.hidden = !shouldShow;
    }

    if (nextButton) {
      nextButton.disabled = state.index >= maxIndex;
      nextButton.hidden = !shouldShow;
    }
  }

  function scrollCompareToIndex(instance, nextIndex, smooth) {
    const scroll = getCompareScrollElement(instance);
    const state = getCompareState(instance);
    const maxIndex = Math.max(0, state.totalColumns - state.visibleColumns);

    state.index = Math.max(0, Math.min(nextIndex, maxIndex));
    updateCompareButtons(instance, state);

    if (!scroll || !state.columnWidth) {
      return;
    }

    const targetLeft = state.index * state.columnWidth;

    if (typeof scroll.scrollTo === 'function') {
      scroll.scrollTo({
        left: targetLeft,
        behavior: smooth ? 'smooth' : 'auto'
      });
    } else {
      scroll.scrollLeft = targetLeft;
    }

    syncCompareHeaderScroll(instance, scroll.scrollLeft);
  }

  function updateCompareLayout(instance, smooth) {
    const scroll = getCompareScrollElement(instance);
    const isMobile = isCompareMobileViewport() && !!instance.querySelector('.cpl-compare-shortcode__mobile-scroll');
    const labelCell = isMobile ? null : instance.querySelector('.cpl-compare-shortcode__label');
    const headerElement = getCompareHeaderElement(instance);
    const state = getCompareState(instance);

    if (!scroll || !state.totalColumns) {
      return;
    }

    state.visibleColumns = isMobile ? 1 : getCompareVisibleColumns(state.totalColumns);
    syncCompareBodyFillers(instance, state.visibleColumns, state.totalColumns);

    if (isMobile) {
      state.columnWidth = Math.max(scroll.clientWidth, 1);
    } else {
      const labelWidth = labelCell ? Math.ceil(labelCell.getBoundingClientRect().width) : 190;
      const minColumnWidth = getCompareMinColumnWidth();
      const availableWidth = Math.max(scroll.clientWidth - labelWidth, minColumnWidth * state.visibleColumns);

      state.columnWidth = Math.max(
        Math.floor(availableWidth / Math.max(state.visibleColumns, 1)),
        minColumnWidth
      );
    }

    instance.style.setProperty('--cpl-compare-column-width', state.columnWidth + 'px');

    if (headerElement) {
      const headerHeight = Math.ceil(headerElement.getBoundingClientRect().height);

      if (headerHeight > 0) {
        instance.style.setProperty('--cpl-compare-header-height', headerHeight + 'px');
      }
    }

    scrollCompareToIndex(instance, state.index, smooth);
    syncCompareHeaderScroll(instance, scroll.scrollLeft);
    updateCompareStickyState(instance);
  }

  function bindCompareInstance(instance) {
    if (!instance || instance.dataset.cplCompareReady === '1') {
      updateCompareLayout(instance, false);
      return;
    }

    instance.dataset.cplCompareReady = '1';

    const prevButton = instance.querySelector('.cpl-compare-shortcode__nav--prev');
    const nextButton = instance.querySelector('.cpl-compare-shortcode__nav--next');
    const scrollElements = instance.querySelectorAll(
      '.cpl-compare-shortcode__scroll, .cpl-compare-shortcode__mobile-scroll'
    );
    const headerImages = instance.querySelectorAll('.cpl-compare-shortcode__vehicle-card img');
    const state = getCompareState(instance);

    if (prevButton) {
      prevButton.addEventListener('click', function () {
        scrollCompareToIndex(instance, state.index - 1, true);
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        scrollCompareToIndex(instance, state.index + 1, true);
      });
    }

    scrollElements.forEach((scrollElement) => {
      scrollElement.addEventListener('scroll', function () {
        if (scrollElement !== getCompareScrollElement(instance)) {
          return;
        }

        syncCompareHeaderScroll(instance, scrollElement.scrollLeft);

        if (!state.columnWidth) {
          return;
        }

        state.index = Math.round(scrollElement.scrollLeft / state.columnWidth);
        updateCompareButtons(instance, state);
      }, { passive: true });
    });

    headerImages.forEach((image) => {
      if (image.complete) {
        return;
      }

      const syncCompareHeader = function () {
        updateCompareLayout(instance, false);
      };

      image.addEventListener('load', syncCompareHeader, { once: true });
      image.addEventListener('error', syncCompareHeader, { once: true });
    });

    updateCompareLayout(instance, false);
  }

  function registerCompareResizeHandler() {
    if (compareResizeRegistered) {
      return;
    }

    compareResizeRegistered = true;
    window.addEventListener('resize', function () {
      document.querySelectorAll('[data-cpl-compare]').forEach((instance) => {
        updateCompareLayout(instance, false);
        updateCompareStickyState(instance);
      });
    });
  }

  function registerCompareStickyHandler() {
    if (compareStickyRegistered) {
      return;
    }

    compareStickyRegistered = true;

    const syncStickyOffset = function () {
      document.querySelectorAll('[data-cpl-compare]').forEach((instance) => {
        updateCompareStickyState(instance);
      });
    };

    window.addEventListener('scroll', syncStickyOffset, { passive: true });
    window.addEventListener('resize', syncStickyOffset, { passive: true });
    window.addEventListener('load', syncStickyOffset, { once: true });
  }

  function initCompareShortcodes(scope) {
    const container = getContainer(scope);
    const instances = container.querySelectorAll('[data-cpl-compare]');

    if (!instances.length) {
      return;
    }

    registerCompareResizeHandler();
    registerCompareStickyHandler();
    instances.forEach((instance) => {
      bindCompareInstance(instance);
      updateCompareStickyState(instance);
    });
  }

  function initCPLButtons(scope) {
    const container = getContainer(scope);
    const buttons = container.querySelectorAll('.cpl-toggle');

    syncSelectionCountWidgetVisibilityFromMarkup(container);
    syncSelectionDependentElementsFromMarkup(container);

    if (buttons.length) {
      ensureVisitorKey();
      applyStateToButtons(container);
    }

    initCompareShortcodes(container);
  }

  function scheduleButtonsRefresh() {
    if (refreshScheduled) {
      return;
    }

    refreshScheduled = true;

    const schedule = typeof window.requestAnimationFrame === 'function'
      ? window.requestAnimationFrame.bind(window)
      : function (callback) {
        return window.setTimeout(callback, 0);
      };

    schedule(function () {
      refreshScheduled = false;
      initCPLButtons(document);
    });
  }

  function registerElementorHook() {
    if (elementorHookRegistered || !window.elementorFrontend || !window.elementorFrontend.hooks) {
      return;
    }

    window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) {
      initCPLButtons($scope && $scope[0] ? $scope[0] : document);
    });

    elementorHookRegistered = true;
  }

  function registerAjaxRefreshHook() {
    if (ajaxHookRegistered || !window.jQuery) {
      return;
    }

    window.jQuery(document).on('ajaxComplete.cpl', function (event, xhr, settings) {
      if (isBPFWEAjaxRequest(settings)) {
        scheduleButtonsRefresh();
      }
    });

    ajaxHookRegistered = true;
  }

  function handleComparePageShow(event) {
    if (!isCompareSelectionPage()) {
      return;
    }

    if (!event.persisted) {
      const navigationEntries = window.performance && typeof window.performance.getEntriesByType === 'function'
        ? window.performance.getEntriesByType('navigation')
        : [];

      if (!navigationEntries.length || navigationEntries[0].type !== 'back_forward') {
        return;
      }
    }

    invalidateCompareCount(document);
    clearCompareHostMarkup(document);
    fetchCompareMarkup();
  }

  function handleCompareCountPageShow(event) {
    if (isCompareSelectionPage()) {
      return;
    }

    if (!event.persisted) {
      const navigationEntries = window.performance && typeof window.performance.getEntriesByType === 'function'
        ? window.performance.getEntriesByType('navigation')
        : [];

      if (!navigationEntries.length || navigationEntries[0].type !== 'back_forward') {
        return;
      }
    }

    invalidateCompareCount(document);
    syncSelectionsFromServer();
  }

  function handleCompareVisibilityChange() {
    if (document.visibilityState === 'hidden') {
      invalidateCompareCount(document);

      if (isCompareSelectionPage()) {
        clearCompareHostMarkup();
      }

      return;
    }

    if (document.visibilityState === 'visible') {
      if (!isCompareSelectionPage()) {
        syncSelectionsFromServer();
        return;
      }

      invalidateCompareCount(document);
      clearCompareHostMarkup(document);
      fetchCompareMarkup();
    }
  }

  function handleComparePageHide() {
    if (!isCompareSelectionPage()) {
      return;
    }

    clearCompareHostMarkup();
  }

  function handleFavoritePageShow(event) {
    if (!isFavoriteSelectionPage()) {
      return;
    }

    if (!event.persisted) {
      const navigationEntries = window.performance && typeof window.performance.getEntriesByType === 'function'
        ? window.performance.getEntriesByType('navigation')
        : [];

      if (!navigationEntries.length || navigationEntries[0].type !== 'back_forward') {
        return;
      }
    }

    invalidateFavoriteCount(document);
    invalidateFavoriteGridScope();
    syncSelectionsFromServer();
  }

  function handleFavoriteCountPageShow(event) {
    if (isFavoriteSelectionPage()) {
      return;
    }

    if (!event.persisted) {
      const navigationEntries = window.performance && typeof window.performance.getEntriesByType === 'function'
        ? window.performance.getEntriesByType('navigation')
        : [];

      if (!navigationEntries.length || navigationEntries[0].type !== 'back_forward') {
        return;
      }
    }

    invalidateFavoriteCount(document);
    syncSelectionsFromServer();
  }

  function handleFavoriteVisibilityChange() {
    invalidateFavoriteCount(document);

    if (document.visibilityState === 'hidden' && isFavoriteSelectionPage()) {
      invalidateFavoriteGridScope();
      return;
    }

    if (document.visibilityState === 'visible' && !isCompareSelectionPage()) {
      syncSelectionsFromServer();
    }
  }

  function handleFavoritePageHide() {
    if (!isFavoriteSelectionPage()) {
      return;
    }

    invalidateFavoriteGridScope();
  }

  document.addEventListener('click', handleDocumentClick);
  window.addEventListener('pageshow', handleComparePageShow);
  window.addEventListener('pageshow', handleCompareCountPageShow);
  window.addEventListener('pageshow', handleFavoritePageShow);
  window.addEventListener('pageshow', handleFavoriteCountPageShow);
  window.addEventListener('pagehide', handleComparePageHide);
  window.addEventListener('pagehide', handleFavoritePageHide);
  document.addEventListener('visibilitychange', handleCompareVisibilityChange);
  document.addEventListener('visibilitychange', handleFavoriteVisibilityChange);
  registerElementorHook();
  registerAjaxRefreshHook();

  if (window.jQuery) {
    window.jQuery(window).on('elementor/frontend/init', registerElementorHook);
  }

  function initOnReady() {
    invalidateCompareCount(document);
    invalidateFavoriteCount(document);
    if (isCompareSelectionPage()) {
      clearCompareHostMarkup(document);
    }
    if (isFavoriteSelectionPage()) {
      invalidateFavoriteGridScope();
    }
    initCPLButtons(document);
    if (isCompareSelectionPage()) {
      fetchCompareMarkup();
    } else {
      syncSelectionsFromServer();
    }
    registerElementorHook();
    registerAjaxRefreshHook();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOnReady);
  } else {
    initOnReady();
  }

  window.CPL_InitButtons = initCPLButtons;
})();
