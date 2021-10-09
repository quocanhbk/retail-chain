import 'package:bkrm/services/info/inventoryInfo/categoryInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/customerFormatter.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class CategoryDetailPage extends StatefulWidget {
  CategoryInfo categoryData;

  CategoryDetailPage(this.categoryData);

  @override
  _CategoryDetailPageState createState() => _CategoryDetailPageState();
}

class _CategoryDetailPageState extends State<CategoryDetailPage> {
  final _formKey = GlobalKey<FormState>();

  bool editEnable = false;

  bool needRefresh=false;

  TextEditingController categoryController = TextEditingController();
  TextEditingController pointRatioController = TextEditingController();

  bool categoryNameValid=false;
  bool pointRatioValid=false;

  bool deleted = false;

  @override
  void initState() {
    super.initState();
    setUpCategoryInfo();
  }

  setUpCategoryInfo(){
    categoryController.text=widget.categoryData.name;
    pointRatioController.text=(widget.categoryData.pointRatio*100).toInt().toString();
  }

  clearPage(){
    categoryController.clear();
    categoryNameValid=true;
  }

  void remoteSetState(){
    setState(() {

    });
  }
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        title: Text("Thông tin danh mục"),
      ),
      body: WillPopScope(
        onWillPop: ()async{
          Navigator.pop(context,needRefresh);
          return needRefresh;
        },
        child: SingleChildScrollView(
          child: Container(
            padding: EdgeInsets.all(8.0),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Text(
                      "Thông tin chi tiết danh mục",
                      style: TextStyle(fontSize: 26, fontWeight: FontWeight.bold),
                    ),
                  ),
                  SizedBox(
                    height: 30,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Tên danh mục: ",
                            style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          enabled: editEnable,
                          controller: categoryController,
                          autovalidateMode: AutovalidateMode.always,
                          validator: (category){
                            if(category==null||category==""){
                              categoryNameValid=false;
                              return " *Bắt buộc";
                            }else{
                              categoryNameValid=true;
                              return null;
                            }
                          },
                          decoration: InputDecoration(
                            enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Colors.blue))
                          ),
                        ),
                      )
                    ],
                  ),
                  SizedBox(
                    height: 30,
                  ),
                  Row(
                    children: [
                      Expanded(
                          flex: 1,
                          child: Text(
                            "Tỷ lệ tích điểm (%): ",
                            style:
                            TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                          )),
                      Expanded(
                        flex: 3,
                        child: TextFormField(
                          inputFormatters: [CustomerFormatter().numberFormatter,LengthLimitingTextInputFormatter(2)],
                          enabled: editEnable,
                          controller: pointRatioController,
                          autovalidateMode: AutovalidateMode.always,
                          keyboardType: TextInputType.number,
                          validator: (pointRatio){
                            if(pointRatio==null||pointRatio==""){
                              pointRatioValid=false;
                              return " *Bắt buộc";
                            }else{
                              pointRatioValid=true;
                              return null;
                            }
                          },
                          decoration: InputDecoration(
                              enabledBorder: UnderlineInputBorder(borderSide: BorderSide(color: Colors.blue))
                          ),
                        ),
                      )
                    ],
                  ),
                   SizedBox(height: 30,),
                  Center(child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: editEnable?
                        [
                          ElevatedButton(
                            onPressed: (){
                              clearPage();
                              setUpCategoryInfo();
                              editEnable=false;
                              setState(() {

                              });
                            },
                            child: Container(
                              padding: EdgeInsets.all(10.0),
                              child: Text("Hủy"),
                            ),
                          ),
                          ElevatedButton(onPressed: ()async{
                          if(editEnable){
                            debugPrint("categoryNameValid");
                            debugPrint(categoryNameValid.toString());
                            _formKey.currentState!.validate();
                            if(categoryNameValid&&pointRatioValid){
                              showDialog(context: context,builder: (context){
                                return AlertDialog(
                                  content: Container(
                                    height: 60,
                                    child: Center(child: CircularProgressIndicator(),),
                                  ),
                                );
                              });
                              double pointRatio = (double.tryParse(pointRatioController.value.text)??0)/100;
                              MsgInfoCode? returnCode = await BkrmService().editCategory(id: widget.categoryData.id,name: categoryController.value.text,pointRatio:pointRatio,deleted: false);
                              Navigator.pop(context);
                              if(returnCode==MsgInfoCode.actionSuccess){
                                widget.categoryData.name=categoryController.value.text;
                                widget.categoryData.pointRatio=pointRatio;
                                showDialog(context: this.context,builder: (context){
                                  return AlertDialog(
                                    title: Text("Thay đổi thông tin danh mục thành công"),
                                    actions: [
                                      FlatButton(onPressed: (){
                                        Navigator.pop(context);
                                        editEnable=false;
                                        needRefresh=true;
                                        this.remoteSetState();
                                      }, child: Text("Đóng"))
                                    ],
                                  );
                                });
                                return;
                              }else{
                                if(returnCode==MsgInfoCode.actionFail){
                                  showDialog(context: this.context,builder: (context){
                                    return AlertDialog(
                                      title: Text("Thay đổi thông tin danh mục thất bại"),
                                      actions: [
                                        FlatButton(onPressed: (){
                                          Navigator.pop(context);
                                        }, child: Text("Đóng"))
                                      ],
                                    );
                                  });
                                  return;
                                }
                                showDialog(context: this.context,builder: (context){
                                  return AlertDialog(
                                    title: Text("Lỗi hệ thống"),
                                    actions: [
                                      FlatButton(onPressed: (){
                                        Navigator.pop(context);
                                      }, child: Text("Đóng"))
                                    ],
                                  );
                                });
                              }
                            }
                          }else{
                            editEnable=true;
                            setState(() {

                            });
                          }
                        },child: Container(padding: EdgeInsets.all(10.0),child: Text(editEnable==false?"Chỉnh sửa":"Xác nhận",style: TextStyle(color:Colors.white,fontSize: 16),)),),
                          RaisedButton(padding: EdgeInsets.all(10.0),color: Colors.red,child: Text("Xoá",style: TextStyle(color:Colors.white,fontSize: 16),),onPressed: (){
                            showDialog(context: context,builder: (context){
                              return AlertDialog(
                                title: Text("Bạn có chắc chắn muốn xoá danh mục này?"),
                                actions: [
                                  FlatButton(onPressed: ()async{
                                    Navigator.pop(context);
                                    showDialog(context: this.context,builder: (context){
                                      return AlertDialog(
                                        content: Container(
                                          height: 60,
                                          child: Center(child: CircularProgressIndicator(),),
                                        ),
                                      );
                                    });
                                    double pointRatio = (double.tryParse(pointRatioController.value.text)??0)/100;
                                    MsgInfoCode? returnCode = await BkrmService().editCategory(id: widget.categoryData.id,name: categoryController.value.text,pointRatio:pointRatio,deleted: true);
                                    if(returnCode==MsgInfoCode.actionSuccess){
                                      showDialog(context: this.context,builder: (context){
                                        return AlertDialog(
                                          title: Text("Xoá danh mục thành công"),
                                          actions: [
                                            TextButton(onPressed: (){
                                              needRefresh=true;
                                              Navigator.pop(context);
                                              Navigator.pop(context);
                                              Navigator.pop(context,needRefresh);
                                            }, child: Text("Đóng"))
                                          ],
                                        );
                                      });
                                    }else{
                                      showDialog(context: context, builder: (context){
                                        return AlertDialog(
                                          title: Text("Xoá danh mục thất bại"),
                                          actions: [
                                            FlatButton(onPressed: (){
                                              Navigator.pop(context);
                                              Navigator.pop(context);
                                            }, child: Text("Đóng"))
                                          ],
                                        );
                                      });
                                    }
                                  }, child: Text("Chắc chắn")),
                                  FlatButton(onPressed: (){Navigator.pop(context);}, child: Text("Huỷ")),
                                ],
                              );
                            });
                          },)
                        ]:
                    [
                      RaisedButton(padding: EdgeInsets.all(10.0),color: Colors.blue,onPressed: ()async{
                      if(editEnable){
                        debugPrint("categoryNameValid");
                        debugPrint(categoryNameValid.toString());
                        _formKey.currentState!.validate();
                        if(categoryNameValid){
                          showDialog(context: context,builder: (context){
                            return AlertDialog(
                              content: Container(
                                height: 60,
                                child: Center(child: CircularProgressIndicator(),),
                              ),
                            );
                          });
                          double pointRatio = (double.tryParse(pointRatioController.value.text)??0)/100;
                          MsgInfoCode? returnCode = await BkrmService().editCategory(id: widget.categoryData.id,name: categoryController.value.text,pointRatio:pointRatio,deleted: false);
                          Navigator.pop(context);
                          if(returnCode==MsgInfoCode.actionSuccess){
                            widget.categoryData.name=categoryController.value.text;
                            widget.categoryData.pointRatio=pointRatio;
                            showDialog(context: this.context,builder: (context){
                              return AlertDialog(
                                title: Text("Thay đổi thông tin danh mục thành công"),
                                actions: [
                                  FlatButton(onPressed: (){
                                    Navigator.pop(context);
                                    editEnable=false;
                                    needRefresh=true;
                                    this.remoteSetState();
                                  }, child: Text("Đóng"))
                                ],
                              );
                            });
                            return;
                          }else{
                            if(returnCode==MsgInfoCode.actionFail){
                              showDialog(context: this.context,builder: (context){
                                return AlertDialog(
                                  title: Text("Thay đổi thông tin danh mục thất bại"),
                                  actions: [
                                    FlatButton(onPressed: (){
                                      Navigator.pop(context);
                                    }, child: Text("Đóng"))
                                  ],
                                );
                              });
                              return;
                            }
                            showDialog(context: this.context,builder: (context){
                              return AlertDialog(
                                title: Text("Lỗi hệ thống"),
                                actions: [
                                  FlatButton(onPressed: (){
                                    Navigator.pop(context);
                                  }, child: Text("Đóng"))
                                ],
                              );
                            });
                          }
                        }
                      }else{
                        editEnable=true;
                        setState(() {

                        });
                      }
                    },child: Text(editEnable==false?"Chỉnh sửa":"Xác nhận",style: TextStyle(color:Colors.white,fontSize: 16),),),
                      RaisedButton(padding: EdgeInsets.all(10.0),color: Colors.red,child: Text("Xoá",style: TextStyle(color:Colors.white,fontSize: 16),),onPressed: (){
                        showDialog(context: context,builder: (context){
                          return AlertDialog(
                            title: Text("Bạn có chắc chắn muốn xoá danh mục này?"),
                            actions: [
                              FlatButton(onPressed: ()async{
                                Navigator.pop(context);
                                showDialog(context: this.context,builder: (context){
                                  return AlertDialog(
                                    content: Container(
                                      height: 60,
                                      child: Center(child: CircularProgressIndicator(),),
                                    ),
                                  );
                                });
                                double pointRatio = (double.tryParse(pointRatioController.value.text)??0)/100;
                                MsgInfoCode? returnCode = await BkrmService().editCategory(id: widget.categoryData.id,name: categoryController.value.text,pointRatio:pointRatio,deleted: true);
                                if(returnCode==MsgInfoCode.actionSuccess){
                                  showDialog(context: this.context,builder: (context){
                                    return AlertDialog(
                                      title: Text("Xoá danh mục thành công"),
                                      actions: [
                                        TextButton(onPressed: (){
                                          needRefresh=true;
                                          Navigator.pop(context);
                                          Navigator.pop(context);
                                          Navigator.pop(context,needRefresh);
                                        }, child: Text("Đóng"))
                                      ],
                                    );
                                  });
                                }else{
                                  showDialog(context: context, builder: (context){
                                    return AlertDialog(
                                      title: Text("Xoá danh mục thất bại"),
                                      actions: [
                                        FlatButton(onPressed: (){
                                          Navigator.pop(context);
                                          Navigator.pop(context);
                                        }, child: Text("Đóng"))
                                      ],
                                    );
                                  });
                                }
                              }, child: Text("Chắc chắn")),
                              FlatButton(onPressed: (){Navigator.pop(context);}, child: Text("Huỷ")),
                            ],
                          );
                        });
                      },)
                    ]
                  )),
                  SizedBox(height: 20,),

                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
