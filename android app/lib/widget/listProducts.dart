import 'dart:async';

import '../services/info/inventoryInfo/itemPagination.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/productItem.dart';
import 'package:flutter/cupertino.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:flutter/material.dart';
import 'package:flutter_slidable/flutter_slidable.dart';
import 'package:bkrm/widget/enumDefine.dart';
import 'package:infinite_scroll_pagination/infinite_scroll_pagination.dart';

class ListProduct extends StatefulWidget {
  TextEditingController editingController = TextEditingController();
  List<ItemInfo>? rawProductsInfo;
  List<ProductItem> processListProducts = [];
  var displayProducts = <ProductItem>[];
  int sumProducts = 0;
  int sumAllQuantityOfProducts = 0;
  late _ListProductState _state;
  bool hasSlider = false;
  BuildContext? parentContext;
  final _doneGetDataController = StreamController();
  Stream get getDataDone => _doneGetDataController.stream;
  double fontSize = 14;
  Function(BuildContext, ItemInfo)? onTapOnProduct;
  Function(BuildContext, ItemInfo)? onLongPressOnProduct;
  String searchQuery = "";
  ListProduct({hasSlider, fontSize = 14.0, this.onTapOnProduct,this.onLongPressOnProduct}) {
    this.hasSlider = hasSlider == null ? false : hasSlider;
    this.fontSize = fontSize;
  }

  void filterSearchResults(String query) {
    searchQuery = query;
    if (query == "") {
      _state.filterSearchResults(null);
    } else {
      _state.filterSearchResults(query);
    }
  }

  void sortProduct({required String orderBy, required String order}) {
    _state.sortProducts(orderBy: orderBy, order: order);
  }

  void filterCategory(int id) {
    if (id == -1) {
      _state.filterCategory(null);
    } else {
      _state.filterCategory(id);
    }
  }
  void refreshList() {
    _state._pagingController.refresh();
  }

  @override
  _ListProductState createState() {
    this._state = _ListProductState();
    return this._state;
  }

  void dispose() {
    _doneGetDataController.close();
  }
}

class _ListProductState extends State<ListProduct> {
  String orderBy = "created_date";
  String order = "desc";
  int? filterCategoryId;
  String? searchQuery;
  String? barcode;
  Future? futureCall;
  SlidableController slideController = SlidableController();
  final PagingController<int, ItemInfo> _pagingController =
      PagingController(firstPageKey: 1);
  initState() {
    super.initState();
    _pagingController.addPageRequestListener((pageKey) {
      _fetchPage(pageKey);
    });
  }

  Future<void> _fetchPage(int pageKey) async {
    try {
      debugPrint("Pagekey " + pageKey.toString());
      debugPrint("Fetch page");
      ItemPage? itemPage = await BkrmService().getAllItem(
          page: pageKey,
          orderBy: orderBy,
          order: order,
          categoryId: filterCategoryId,
          searchQuery: searchQuery);
      if (itemPage == null) {
        _pagingController.appendLastPage([]);
        return;
      }
      if (itemPage.currentPage == itemPage.lastPage) {
        debugPrint("last page");
        _pagingController.appendLastPage(itemPage.items);
      } else {
        final nextPageKey = pageKey + 1;
        _pagingController.appendPage(itemPage.items, nextPageKey);
      }
    } catch (error) {
      _pagingController.error = error;
    }
  }

  void sortProducts({required String orderBy, required String order}) {
    this.orderBy = orderBy;
    this.order = order;
    _pagingController.refresh();
  }

  void filterCategory(int? id) {
    filterCategoryId = id;
    _pagingController.refresh();
  }

  //Fetch raw data of products
  //Filter product to match with search phrase
  void filterSearchResults(String? query) {
    searchQuery = query;
    _pagingController.refresh();
  }

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: RefreshIndicator(
        onRefresh: ()async{
          _pagingController.refresh();
        },
        child: PagedListView.separated(
          shrinkWrap: true,
          pagingController: _pagingController,
          builderDelegate: PagedChildBuilderDelegate(
            itemBuilder: (BuildContext context, ItemInfo item, int index) {
              return Container(
                padding: EdgeInsets.fromLTRB(4.0,0,4.0,0),
                child: ProductItem(
                  item,
                  pagingColtroller: _pagingController,
                  onTapOnProduct: widget.onTapOnProduct,
                  hasSlider: widget.hasSlider,
                  slideController: slideController,
                  onLongPressedOnProduct: widget.onLongPressOnProduct,
                ),
              );
            },
            noItemsFoundIndicatorBuilder: (context) {
              return Container(
                height: MediaQuery.of(context).size.height / 2,
                child: Center(
                  child: Text(
                    "Không có sản phẩm",
                    style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w300,
                        color: Colors.grey),
                  ),
                ),
              );
            },
            firstPageErrorIndicatorBuilder: (context) {
              return Container(
                height: MediaQuery.of(context).size.height / 2,
                child: Center(
                  child: Text(
                    "Đã có lỗi xảy ra",
                    style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w300,
                        color: Colors.red),
                  ),
                ),
              );
            },
          ),
          separatorBuilder: (BuildContext context, int index) {
            return Divider();
          },
        ),
      ),
    );
  }
}
