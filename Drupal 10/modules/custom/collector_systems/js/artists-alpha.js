(function (Drupal, once) {
  Drupal.behaviors.artistsAlpha = {
    attach: function (context, settings) {
      var artistSettings = settings.collectorSystems && settings.collectorSystems.artistsAlpha;
      if (!artistSettings) return;

      once('cs-artists-alpha', '#cs-artists-alpha-container', context).forEach(function () {
        var isLoading   = false;
        var hasMore     = artistSettings.hasMore;
        var currentPage = artistSettings.currentPage;
        var ajaxUrl     = artistSettings.ajaxUrl;

        function csAjaxUrl(pageNo) {
          var sort     = document.getElementById('seldataorderby') ? document.getElementById('seldataorderby').value : 'ArtistName%20asc';
          var charVal  = artistSettings.char;
          var searchEl = document.getElementById('searchindata');
          var search   = searchEl ? searchEl.value : '';
          var parts    = ['pageNo=' + pageNo, 'sortBy=' + sort];
          if (charVal) parts.push('char=' + encodeURIComponent(charVal));
          if (search)  parts.push('qSearch=' + encodeURIComponent(search));
          return ajaxUrl + '?' + parts.join('&');
        }

        function csBuildPageUrl(overrides) {
          var sort    = overrides.sort   !== undefined ? overrides.sort   : (document.getElementById('seldataorderby') ? document.getElementById('seldataorderby').value : 'ArtistName%20asc');
          var charVal = overrides.char   !== undefined ? overrides.char   : artistSettings.char;
          var search  = overrides.search !== undefined ? overrides.search : '';
          var parts   = ['sortBy=' + sort];
          if (charVal) parts.push('char=' + encodeURIComponent(charVal));
          if (search)  parts.push('qSearch=' + encodeURIComponent(search));
          return window.location.pathname + '?' + parts.join('&');
        }

        window.csAlphaFilter = function (letter) {
          window.location.href = csBuildPageUrl({ char: letter });
        };

        window.csClearAlphaFilter = function () {
          window.location.href = csBuildPageUrl({ char: '' });
        };

        window.csApplySortingAlpha = function () {
          var searchEl = document.getElementById('searchindata');
          window.location.href = csBuildPageUrl({ search: searchEl ? searchEl.value : '' });
        };

        function mergeArtistGroups(html) {
          var container = document.getElementById('cs-artists-alpha-container');
          if (!container) return;
          var temp = document.createElement('div');
          temp.innerHTML = html;
          Array.from(temp.children).forEach(function (child) {
            if (child.classList.contains('letter-header')) {
              var letter = child.getAttribute('data-letter');
              if (!container.querySelector('.letter-header[data-letter="' + letter + '"]')) {
                container.appendChild(child.cloneNode(true));
              }
            } else if (child.hasAttribute('data-letter-group')) {
              var letter = child.getAttribute('data-letter-group');
              var existing = container.querySelector('[data-letter-group="' + letter + '"]');
              if (existing) {
                Array.from(child.children).forEach(function (card) {
                  existing.appendChild(card.cloneNode(true));
                });
              } else {
                container.appendChild(child.cloneNode(true));
              }
            }
          });
        }

        function loadMoreArtists() {
          if (isLoading || !hasMore) return;
          isLoading = true;
          currentPage++;

          var loader = document.getElementById('cs-artists-loader');
          if (loader) loader.style.display = 'block';

          fetch(csAjaxUrl(currentPage), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
          })
          .then(function (response) { return response.json(); })
          .then(function (data) {
            if (data.html) {
              mergeArtistGroups(data.html);
            }
            hasMore = data.hasMore;
            if (!hasMore) {
              if (observer) observer.disconnect();
              var end = document.getElementById('cs-artists-end');
              if (end) end.style.display = 'block';
            }
            if (typeof window.applyCsCustomizations === 'function') {
              window.applyCsCustomizations();
            }
          })
          .catch(function (err) {
            console.error('Error loading more artists:', err);
            currentPage--;
          })
          .finally(function () {
            if (loader) loader.style.display = 'none';
            isLoading = false;
          });
        }

        var observer = null;
        if (hasMore && 'IntersectionObserver' in window) {
          var sentinel = document.getElementById('cs-artists-sentinel');
          if (sentinel) {
            observer = new IntersectionObserver(function (entries) {
              if (entries[0].isIntersecting && !isLoading && hasMore) {
                loadMoreArtists();
              }
            }, { rootMargin: '200px' });
            observer.observe(sentinel);
          }
        }
      });
    }
  };
}(Drupal, once));
