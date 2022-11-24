----------------------------------  f_arr_cant_por_despachar ------------------------------------
alter FUNCTION f_arr_cant_por_despachar(@ve_cod_item_arriendo numeric, @ve_filtro varchar(20)=NULL)
RETURNS T_CANTIDAD 
AS
BEGIN

	declare 
		@cantidad 				T_CANTIDAD
		,@cantidad_despachada	T_CANTIDAD
		,@res					T_CANTIDAD

	select @cantidad = cantidad
	from item_arriendo 
	where cod_item_arriendo = @ve_cod_item_arriendo
	

	--total despachado
	select @cantidad_despachada = isnull(sum(cantidad), 0) 
	from item_guia_despacho igd, guia_despacho gd 
	where igd.cod_item_doc = @ve_cod_item_arriendo and
		  igd.tipo_doc = 'ITEM_ARRIENDO' and
			igd.cod_guia_despacho = gd.cod_guia_despacho and
		((gd.cod_estado_doc_sii in (2, 3)) or ( gd.cod_estado_doc_sii =1 and @ve_filtro = 'TODO_ESTADO'))

	

	if (@cantidad <= @cantidad_despachada)
		set @res = 0
	else
		set @res = @cantidad - @cantidad_despachada
		
	return @res;
	
END
go