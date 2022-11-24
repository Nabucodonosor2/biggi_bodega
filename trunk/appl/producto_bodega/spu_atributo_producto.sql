-------------------- spu_atributo_producto ---------------------------------
CREATE PROCEDURE dbo.spu_atributo_producto
		(@ve_operacion varchar(20)
		,@ve_cod_atributo_producto	numeric
		,@ve_nom_atributo_producto	varchar(1000)=NULL
		,@ve_cod_producto			varchar(30)=NULL
		,@ve_orden					numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into atributo_producto
							(nom_atributo_producto
							,cod_producto
							,orden)
			values 			(@ve_nom_atributo_producto
							,@ve_cod_producto
							,@ve_orden)
		end 
	else if (@ve_operacion='UPDATE') 
		begin
			update atributo_producto
			set		nom_atributo_producto	= @ve_nom_atributo_producto
					,cod_producto			= @ve_cod_producto
					,orden					= @ve_orden
			where	cod_atributo_producto	= @ve_cod_atributo_producto	
		end 
	else if(@ve_operacion='DELETE') 
		begin
			delete atributo_producto
			where	cod_atributo_producto	= @ve_cod_atributo_producto
		end 
	
END
go