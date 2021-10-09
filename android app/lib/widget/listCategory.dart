import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/listProducts.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../services/info/inventoryInfo/categoryInfo.dart';

class ListCategory extends StatefulWidget {
  ListProduct listProduct;
  int? categoryId = -1;
  @override
  _ListCategoryState createState() => _ListCategoryState();

  ListCategory(this.listProduct);
}

class _ListCategoryState extends State<ListCategory> {
  List<Widget> listCategory = [Container()];
  @override
  void initState() {
    super.initState();
    buildListCategory();
  }

  void buildListCategory() async {
    listCategory.clear();
    BkrmService bkrmService = BkrmService();
    List<CategoryInfo> listDataCategory = await (bkrmService.getCategory());
    listCategory.add(Row(
      children: [
        Expanded(flex: 2, child: Text("Tất cả")),
        Expanded(
          flex: 1,
          child: Radio(
            groupValue: widget.categoryId,
            value: -1,
            onChanged: (dynamic value) {
              setState(() {
                widget.categoryId = value;
                widget.listProduct.filterCategory(-1);
                Navigator.pop(context);
              });
            },
          ),
        )
      ],
    ));
    listDataCategory.forEach((element) {
      listCategory.add(Row(
        children: [
          Expanded(flex: 2, child: Text(element.name)),
          Expanded(
            flex: 1,
            child: Radio(
              groupValue: widget.categoryId,
              value: element.id,
              onChanged: (dynamic value) {
                setState(() {
                  widget.categoryId = value;
                  widget.listProduct.filterCategory(element.id!);
                  Navigator.pop(context);
                });
              },
            ),
          )
        ],
      ));
    });
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text("Danh mục"),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: listCategory.isEmpty
              ? [
                  Container(
                    child: Center(
                      child: CircularProgressIndicator(),
                    ),
                  )
                ]
              : listCategory,
        ),
      ),
    );
  }
}
