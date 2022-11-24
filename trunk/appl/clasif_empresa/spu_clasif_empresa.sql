-------------------- spu_clasif_empresa---------------------------------
CREATE PROCEDURE [dbo].[spu_clasif_empresa](@ve_operacion varchar(20),@ve_cod_clasif_empresa numeric, @ve_nom_clasif_empresa varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into clasif_empresa (nom_clasif_empresa, orden)
		values (@ve_nom_clasif_empresa, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update clasif_empresa 
		set nom_clasif_empresa = @ve_nom_clasif_empresa, orden = @ve_orden
    	where cod_clasif_empresa = @ve_cod_clasif_empresa
	end
	else if (@ve_operacion='DELETE') begin
		delete clasif_empresa 
    	where cod_clasif_empresa = @ve_cod_clasif_empresa
	end
	
	EXECUTE sp_orden_parametricas 'CLASIF_EMPRESA'
END
go
