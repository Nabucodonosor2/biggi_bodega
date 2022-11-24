alter PROCEDURE sp_fa_crear_desde_nv_anticipo(@ve_cod_nota_venta numeric
												,@ve_cod_usuario numeric)
AS
BEGIN  
	declare 
		@vl_cod_fa		numeric

	execute sp_fa_crear @ve_cod_nota_venta, @ve_cod_usuario	
	set @vl_cod_fa = @@identity
	
	insert into item_factura (
		COD_FACTURA, 
		ORDEN, 
		ITEM, 
		COD_PRODUCTO, 
		NOM_PRODUCTO, 
		CANTIDAD, 
		PRECIO, 
		COD_ITEM_DOC,
		TIPO_DOC
	)
	values(
		@vl_cod_fa,
		10,
		'1',
		'TE',
		'__ANTICIPO__',
		1,
		0,
		null,
		null) 
		
	execute spu_factura'RECALCULA', @vl_cod_fa
END