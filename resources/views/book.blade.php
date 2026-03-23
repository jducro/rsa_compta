@extends('layout')

@section('container')
<div class="container-fluid">
@endsection

@section('body')
  <table id="book" class="table table-striped" style="width:100%">
    <thead>
      <tr>
        <th></th>
        <th>Type</th>
        <th>Date</th>
        <th>Nom</th>
        <th>Libellé</th>
        <th>Débit</th>
        <th>Crédit</th>
        <th>Ventilation</th>
        <th>Renouv.</th>
        <th>F. Client</th>
        <th>Cot. RSANav</th>
        <th>Cot. RSA</th>
        <th>Suivi Nav</th>
        <th>Rbt PEN</th>
        <th title="Reunion / Seminaire PEN">Reu/Sem Pen</th>
        <th>Frais Paypal</th>
        <th>Frais Sogecom</th>
        <th>OSAC</th>
        <th>Autres Charges</th>
        <th>Dons</th>
        <th>Vib Deb.</th>
        <th>Vib Cred.</th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th></th>
        <th>Type</th>
        <th>Date</th>
        <th>Nom</th>
        <th>Libellé</th>
        <th>Débit</th>
        <th>Crédit</th>
        <th>Ventilation</th>
        <th>Renouv.</th>
        <th>F. Client</th>
        <th>Cot. RSANav</th>
        <th>Cot. RSA</th>
        <th>Suivi Nav</th>
        <th>Rbt PEN</th>
        <th title="Reunion / Seminaire PEN">Reu/Sem Pen</th>
        <th>Frais Paypal</th>
        <th>Frais Sogecom</th>
        <th>OSAC</th>
        <th>Autres Charges</th>
        <th>Dons</th>
        <th>Vib Deb.</th>
        <th>Vib Cred.</th>
      </tr>
    </tfoot>
  </table>
  <div>
    <a href="{{ route('excel') }}" class="btn btn-secondary"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
  </div>

  <script type="text/javascript">
    function moneyFormat(data) {
      if (!data) {
        return '';
      }
      return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(data);
    }
    let bookTable = $('#book').DataTable({
      ajax: {
        url: '{{ route('lines') }}',
        type: "POST",
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
      },
      orderCellsTop: true,
      fixedHeader: true,
      processing: true,
      serverSide: true,
      stateSave: true,
      stateSaveCallback: function (settings, data) {
        localStorage.setItem( 'DataTables', JSON.stringify(data) );
      },
      stateLoadCallback: function (settings) {
        return JSON.parse( localStorage.getItem('DataTables') );
      },
      language: {
        url: '/datatable_fr.json',
      },
      order: [[2, 'asc']],
      pageLength: 20,
      lengthMenu: [
        [10, 20, 50, -1],
        [10, 20, 50, 'Toutes']
      ],
      columns: [
        {
          data: 'id',
          render: function(data, type, row) {
            let url = '{{ route('line', ['id' => 0]) }}';
            url = url.replace('/0', '/' + data);
            return '<a href="'+url+'" style="white-space: nowrap;"><i class="bi bi-pencil-square"></i> Editer</a>';
          },
          sortable: false,
        },
        {data: 'type'},
        {data: 'date',
          render: function(data, type, row) {
            const date = new Date(data);
            return dateFns.format(date, 'DD/MM/YYYY');
          }
        },
        {data: 'name'},
        {data: 'label'},
        {
          data: 'debit',
          className: 'dt-right font-weight-bold',
          render : function (data) {
            return '<strong>'+moneyFormat(data)+'</strong>';
          }
        },
        {
          data: 'credit',
          className: 'dt-right font-weight-bold',
          render : function (data) {
            return '<strong>'+moneyFormat(data)+'</strong>';
          }
        },
        {
          data: 'breakdown',
          render: function (data) {
            return data?.length > 0 ? '<span style="color: green;">&check;</span>' : '<span style="color: red;">&cross;</span>';
          }
        },
        {
          data: 'breakdown_plane_renewal',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_customer_fees',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_rsa_nav_contribution',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_rsa_contribution',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_follow_up_nav',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_pen_refund',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_meeting',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_paypal_fees',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_sogecom_fees',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_osac',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_other_fees',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_donation',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_vibration_debit',
          render : function (data) {
            return moneyFormat(data);
          }
        },
        {
          data: 'breakdown_vibration_credit',
          render : function (data) {
            return moneyFormat(data);
          }
        },
      ],
      drawCallback: function () {
          let api = this.api();
          let container = $(api.table().container());

          // Re-inject go-to-page input after every draw (DataTables 2.x re-renders .dt-paging on draw)
          let paginate = container.find('.dt-paging');
          if (paginate.length && !paginate.find('.dt-goto-page').length) {
              let wrapper = $('<span class="dt-goto-page" style="margin-left: 10px; vertical-align: middle; display: inline-block;"></span>');
              let gotoLabel = $('<span> Page </span>');
              let gotoInput = $('<input type="number" min="1" style="width: 60px; margin: 0 4px;" />');

              gotoInput.attr('max', api.page.info().pages);
              gotoInput.val(api.page.info().page + 1);

              gotoInput.on('keydown', function (e) {
                  if (e.key === 'Enter') {
                      let page = parseInt($(this).val()) - 1;
                      let totalPages = api.page.info().pages;
                      if (page >= 0 && page < totalPages) {
                          api.page(page).draw('page');
                      }
                  }
              });

              wrapper.append(gotoLabel).append(gotoInput);
              paginate.append(wrapper);
          } else {
              // Update max and current value on re-draw
              paginate.find('.dt-goto-page input')
                  .attr('max', api.page.info().pages)
                  .val(api.page.info().page + 1);
          }
      },
      initComplete: function () {
        let api = this.api();

        // Create per-column filter inputs
        api.columns().every(function () {
            let column = this;
            let title = column.footer().textContent;

            // Create input element
            let input = document.createElement('input');
            input.placeholder = title;
            column.footer().replaceChildren(input);

            // Restore saved filter value from state
            input.value = column.search();

            // Event listener for user input
            input.addEventListener('keyup', () => {
                if (column.search() !== input.value) {
                    column.search(input.value).draw();
                }
            });
        });
      }
    });
  </script>
@endsection