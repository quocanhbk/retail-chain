import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/pages/inventoryModule/item/itemDetailPage.dart';
import 'package:bkrm/pages/sellerModule/shoppingCart/shoppingCart.dart';
import 'package:bkrm/services/api.dart';
import 'package:bkrm/services/services.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:flutter/material.dart';
import 'package:flutter_slidable/flutter_slidable.dart';
import 'package:infinite_scroll_pagination/infinite_scroll_pagination.dart';
import 'package:intl/intl.dart';
import 'package:page_transition/page_transition.dart';

class ProductItem extends StatelessWidget {

  String? imageLink, productName, productCode, productPrice, productAmount;
  final ItemInfo rawDataItem;
  late bool hasSlider;
  SlidableController? controller;
  double fontSize = 12;
  void Function(BuildContext, ItemInfo)? onTapOnProduct;
  void Function(BuildContext,ItemInfo)? onLongPressedOnProduct;
  PagingController? _pagingController;

  NumberFormat formatter = NumberFormat();

  ProductItem(this.rawDataItem,
      {PagingController? pagingColtroller,
      hasSlider,
      slideController,
      fontSize,
      this.onTapOnProduct,
      this.onLongPressedOnProduct}) {
    imageLink = rawDataItem.imageUrl;
    productName = rawDataItem.itemName;
    // productUnit=rawDataItem.unitName;
    productCode = rawDataItem.barCode;
    productPrice = formatter.format(rawDataItem.sellPrice);
    productAmount = rawDataItem.quantity.toString();
    this.hasSlider = hasSlider == null ? false : hasSlider;
    this.controller = slideController == null ? null : slideController;
    this.fontSize = fontSize == null ? 12 : fontSize;
    this._pagingController = pagingColtroller;
    this.onTapOnProduct = onTapOnProduct;
  }

  @override
  Widget build(BuildContext context) {
    return Slidable(
      enabled: hasSlider,
      controller: controller == null ? null : controller,
      actionPane: SlidableScrollActionPane(),
      actions: [
        FlatButton(
          onPressed: () {
            BkrmService bkrmService = BkrmService();
            bkrmService.cart!.addCartItem(rawDataItem, 1);
            Navigator.push(context, PageTransition(child: ShoppingCart(), type: pageTransitionType));
          },
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Icon(
              Icons.add_shopping_cart,
              color: Colors.blueAccent,
            ),
            Text(
              "Thêm vào giỏ hàng",
              style: TextStyle(fontSize: 10),
              textAlign: TextAlign.center,
            )
          ]),
        ),
        FlatButton(
          onPressed: () {
            Navigator.push(
                context,
                PageTransition(child: ItemDetailPage(rawDataItem), type: pageTransitionType)).then((value) {
              if (value != null) {
                if (value) {
                  if (_pagingController != null) {
                    _pagingController!.refresh();
                  }
                }
              }
            });
          },
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Icon(
              Icons.info,
              color: Colors.blueAccent,
            ),
            Text(
              "Xem thông tin chi tiết",
              style: TextStyle(fontSize: 10),
              textAlign: TextAlign.center,
            )
          ]),
        )
      ],
      child: InkWell(
        onTap: () {
          if (onTapOnProduct != null) {
            onTapOnProduct!(context, rawDataItem);
          } //Using rawDataItem to pass item info
        },
        onLongPress: (){
          if(onLongPressedOnProduct!=null){
            onLongPressedOnProduct!(context,rawDataItem);
          }
        },
        child: Container(
          decoration: BoxDecoration(
              border: Border(
                  bottom: BorderSide(color: Colors.black12, width: 2.0))),
          child: Row(
            children: [
              Expanded(
                  flex: 2,
                  child: imageLink == null
                      ? Image.asset(
                          "asset/productImage/no-image.jpg",
                          width: 70,
                          height: 70,
                        )
                      : CachedNetworkImage(
                          imageUrl: ServerConfig.projectUrl + imageLink!,
                          height: 70,
                          width: 70,
                          progressIndicatorBuilder: (context, url,downloadProgress) =>
                              Container(child: SizedBox(height:20,width:20,child: Center(child: CircularProgressIndicator(value: downloadProgress.progress,)))),
                          errorWidget: (context, url, error) =>
                              Icon(Icons.error),
                        )),
              Expanded(
                  flex: 5,
                  child: Padding(
                    padding: const EdgeInsets.all(3.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          productName!,
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: fontSize),
                        ),
                        Padding(padding: EdgeInsets.all(2.0)),
/*                        productCode == null
                            ? Container()
                            : Text(
                                productCode!,
                                style: TextStyle(
                                    fontSize: fontSize,
                                    color: Colors.blueAccent,
                                    fontWeight: FontWeight.bold),
                              ),
                        Padding(padding: EdgeInsets.all(2.0)),*/
                        // Text(
                        //   "Đơn vị :"+productUnit,
                        //   style: TextStyle(fontWeight: FontWeight.bold,fontSize: fontSize),
                        // ),
                      ],
                    ),
                  )),
              Expanded(
                  flex: 3,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        productPrice! + " VNĐ",
                        style: TextStyle(fontSize: fontSize),
                      ),
                      Padding(padding: EdgeInsets.all(2.0)),
                      Text(
                        "Số lượng: " + productAmount!,
                        style: TextStyle(fontSize: fontSize),
                      )
                    ],
                  ))
            ],
          ),
        ),
      ),
    );
  }
}
