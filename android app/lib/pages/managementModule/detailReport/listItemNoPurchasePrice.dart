import 'package:bkrm/pages/inventoryModule/item/itemDetailPage.dart';
import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/productItem.dart';
import 'package:flutter/material.dart';
import 'package:page_transition/page_transition.dart';

import 'package:bkrm/pages/Nav2App.dart';

class ListItemNoPurchasePrice extends StatefulWidget {
  List<ItemInfo> listItems;
  ListItemNoPurchasePrice(this.listItems);

  @override
  _ListItemNoPurchasePriceState createState() => _ListItemNoPurchasePriceState();
}

class _ListItemNoPurchasePriceState extends State<ListItemNoPurchasePrice> {
  GlobalKey<RefreshIndicatorState> _refreshKey = GlobalKey();
  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: ()async {
        Navigator.pop(context,widget.listItems.isEmpty);
        return widget.listItems.isEmpty;
      },
      child: Scaffold(
        appBar: AppBar(title: Text("Sản phẩm không có giá nhập"),),
        body: RefreshIndicator(
          key: _refreshKey,
          onRefresh: ()async{
            var itemsWithNoPurchasePrice = await BkrmService().getItemsWithNoPurchasePrice();
            setState(() {
              widget.listItems=itemsWithNoPurchasePrice;
            });
          },
          child: widget.listItems.isNotEmpty?ListView.separated(itemBuilder: (context,index){
            return ProductItem(widget.listItems[index],hasSlider: false,onTapOnProduct: (context,ItemInfo item){
              Navigator.push(context, PageTransition(child:  ItemDetailPage(item), type: pageTransitionType)).then((value) {
                if(value!=null){
                  if(value){
                    _refreshKey.currentState!.show();
                  }
                }
              });
            },);
          }, separatorBuilder: (context,index){return Divider();}, itemCount: widget.listItems.length):
          Expanded(child: Center(child: Text("Không còn sản phẩm không có giá nhập",style: TextStyle(fontSize: 18,fontWeight: FontWeight.w300,color: Colors.grey),),)),
        ),
      ),
    );
  }
}
